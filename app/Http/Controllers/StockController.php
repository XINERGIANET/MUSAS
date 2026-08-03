<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use App\Models\Movement;
use App\Models\MovementDetail;
use App\Models\Product;
use App\Models\ProductionDetail;
use App\Models\Stock;
use App\Models\StockInicial;
use App\Models\Storage3;
use App\Models\SaleDetail;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;

class StockController extends Controller
{
    /**0
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $headquarters = Headquarters::select('id', 'nombre')
            ->where('estado', 0)
            ->orderBy('nombre')
            ->get();

        $start_date = $request->start_date ?? now()->format('Y-m-d');
        $end_date   = $request->end_date ?? now()->format('Y-m-d');
        $turno      = $request->turno;
        $sede = $request->headquarter_id ?: auth()->user()->headquarter->id ?? null;
        // Productos
        $products = Product::where('estado', 0)
            ->where('category_id', 3)
            ->get();

        $movimientosPorProducto = [];

        $movementDetails = MovementDetail::with('movement')
            ->whereHas('movement', function ($q) use ($start_date, $end_date, $turno, $sede) {
                $q->whereBetween(DB::raw('DATE(date)'), [$start_date, $end_date])
                    ->where('estado', 0)
                    ->whereIn('tipo', [1, 2, 3, 4, 6])
                    ->when($turno !== null && $turno !== '', function($q) use ($turno) { 
                        return $q->where('turno', $turno); 
                    })
                    ->where(function ($sub) use ($sede) {
                        $sub->where('headquarter_id', $sede)
                            ->orWhere('headquarter_to_id', $sede);
                    });
            })
            ->where('estado', 0)
            ->get();

        foreach ($movementDetails as $detail) {
            $productId = $detail->product_id;
            $tipo = $detail->movement->tipo;

            if (!isset($movimientosPorProducto[$productId])) {
                $movimientosPorProducto[$productId] = [
                    'entrada' => 0,
                    'salida' => 0,
                ];
            }

            // ENTRADAS
            if ($tipo == 6 && $detail->movement->headquarter_id == $sede) {
                // Ingreso por producción
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }
            if ($tipo == 2 && $detail->movement->headquarter_to_id == $sede) {
                // Traslado recibido
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }
            if ($tipo == 1 && $detail->transformado == 1 && $detail->movement->headquarter_id == $sede) {
                // Transformación porción
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }

            // SALIDAS
            if ($tipo == 3 && $detail->movement->headquarter_id == $sede) {
                // Merma
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 4 && $detail->movement->headquarter_id == $sede) {
                // Retoque
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 2 && $detail->movement->headquarter_id == $sede) {
                // Traslado enviado
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 1 && $detail->transformado == 0 && $detail->movement->headquarter_id == $sede) {
                // Transformación base
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
        }

        // Traer el stock principal
        $stock = Stock::with(['product', 'headquarter'])
            ->whereNotNull('venta_real')
            ->when($start_date, function($q) use ($start_date) { return $q->whereDate('fecha', '>=', $start_date); })
            ->when($end_date, function($q) use ($end_date) { return $q->whereDate('fecha', '<=', $end_date); })
            ->when($sede, function($q) use ($sede) { return $q->where('headquarter_id', $sede); })
            ->when($turno !== null && $turno !== '', function($q) use ($turno) { return $q->where('turno', $turno); })
            ->orderBy('fecha', 'desc')
            ->paginate(30)
            ->appends($request->only(['start_date', 'end_date', 'headquarter_id', 'turno']));

        return view('stock.index', compact('stock', 'headquarters', 'movimientosPorProducto', 'products'));
    }

    public function pdf($beginDate, $endDate, $sede = null, $turno)
    {
        $start_date  = $beginDate . ' 00:00:00';
        //$end_date = $endDate . ' 23:59:59';
        $sede = $sede === 'null' ? null : $sede;


        $stock = Stock::with('product')
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('fecha', $start_date);
            })
            ->when($sede, function($q) use ($sede) { return $q->where('headquarter_id', $sede); })
            ->when($turno !== null, function($q) use ($turno) { return $q->where('turno', $turno); })
            ->select('*') //no sé pq tengo que poner esto
            ->selectSub(function ($query) use ($sede) {
                $query->from('product_price')
                    ->whereColumn('product_price.product_id', 'paloteo.product_id')
                    ->where('product_price.headquarter_id', $sede)
                    ->select('unit_price')
                    ->limit(1);
            }, 'precio_actual')
            ->get();

        $user = auth()->user()->email;

        $nombre_sede = optional(Headquarters::find($sede))->nombre;

        $filterInfo = [
            'startDate' => $beginDate,
            //'endDate' => $endDate,
            'sede' => $nombre_sede,
            'turno' => $turno,
        ];

        $pdf = Pdf::loadView('stock.pdf', compact('stock', 'user', 'filterInfo'));
        return $pdf->download('Stock final.pdf');
    }

    // ...existing code...

    /**
     * Obtiene los precios actuales de los productos por sede y calcula los totales.
     */
    protected function calcularTotalesStock($stock, $sede)
    {
        $total = 0;
        $totalteorico = 0;

        // Obtener precios actuales por producto y sede
        $precios = DB::table('product_price')
            ->where('headquarter_id', $sede)
            ->pluck('unit_price', 'product_id');

        foreach ($stock as $item) {
            $precio = $precios[$item->product_id] ?? $item->unit_price ?? 0;
            $item->precio_actual = $precio;
            $total += ($item->venta_real ?? 0) * $precio;
            $totalteorico += ($item->venta_teorica ?? 0) * $precio;
        }

        return [
            'total' => $total,
            'totalteorico' => $totalteorico,
            'stock' => $stock
        ];
    }

    public function pdfGeneral($beginDate, $endDate, $sede = null, $turno = null)
    {
        $start_date = $beginDate;
        $end_date = $endDate;
        $sede = $sede === 'null' ? null : $sede;
        $turno = ($turno === 'null' || $turno === '') ? null : $turno;

        // Productos
        $products = Product::where('estado', 0)
            ->where('category_id', 3)
            ->orderBy('nombre', 'asc')
            ->get();

        // Movimientos por producto (igual que index)
        $movimientosPorProducto = [];

        $movementDetails = MovementDetail::with('movement')
            ->whereHas('movement', function ($q) use ($start_date, $end_date, $turno, $sede) {
                $q->whereBetween(DB::raw('DATE(date)'), [$start_date, $end_date])
                    ->where('estado', 0)
                    ->whereIn('tipo', [1, 2, 3, 4, 6])
                    ->when($turno !== null && $turno !== '', function($q) use ($turno) { return $q->where('turno', $turno); })
                    ->where(function ($sub) use ($sede) {
                        $sub->where('headquarter_id', $sede)
                            ->orWhere('headquarter_to_id', $sede);
                    });
            })
            ->where('estado', 0)
            ->get();

        foreach ($movementDetails as $detail) {
            $productId = $detail->product_id;
            $tipo = $detail->movement->tipo;

            if (!isset($movimientosPorProducto[$productId])) {
                $movimientosPorProducto[$productId] = [
                    'entrada' => 0,
                    'salida' => 0,
                ];
            }

            // ENTRADAS
            if ($tipo == 6 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }
            if ($tipo == 2 && $detail->movement->headquarter_to_id == $sede) {
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }
            if ($tipo == 1 && $detail->transformado == 1 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['entrada'] += $detail->quantity;
            }

            // SALIDAS
            if ($tipo == 3 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 4 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 2 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
            if ($tipo == 1 && $detail->transformado == 0 && $detail->movement->headquarter_id == $sede) {
                $movimientosPorProducto[$productId]['salida'] += $detail->quantity;
            }
        }

        // Traer el stock principal (igual que index)
        $stock = Stock::with(['product', 'headquarter'])
            ->whereNotNull('venta_real')
            ->when($start_date, function($q) use ($start_date) { return $q->whereDate('fecha', '>=', $start_date); })
            ->when($end_date, function($q) use ($end_date) { return $q->whereDate('fecha', '<=', $end_date); })
            ->when($sede, function($q) use ($sede) { return $q->where('headquarter_id', $sede); })
            ->when($turno !== null && $turno !== '', function($q) use ($turno) { return $q->where('turno', $turno); })
            ->select('*')
            ->join('products', 'paloteo.product_id', '=', 'products.id')
            ->orderBy('products.nombre', 'asc')
            ->get();

        $user = auth()->user()->email;
        $nombre_sede = optional(Headquarters::find($sede))->nombre;

        $filterInfo = [
            'startDate' => $beginDate,
            'endDate' => $endDate,
            'sede' => $nombre_sede,
            'turno' => $turno,
        ];

        // Lógica de precios y totales
        $totales = $this->calcularTotalesStock($stock, $sede);

        $pdf = Pdf::loadView('stock.pdfGeneral', compact(
            'stock',
            'user',
            'filterInfo',
            'movimientosPorProducto',
            'products',
            'totales'
        ));

        return $pdf->download('Reporte paloteo.pdf');
    }

    // ...existing code...
    public function create(Request $request)
    {
        $user = auth()->user();
        $rolId = $user->rol_id;
    
        $fecha = now()->format('Y-m-d');
        $turno = $user->turno;
        $headquarter_id = $user->sede_id;
    
        Log::debug('📥 Entrada a stock.create', [
            'usuario_id' => $user->id,
            'rol_id' => $rolId,
            'sede_id' => $user->sede_id,
            'headquarter_input' => $headquarter_id,
            'fecha' => $fecha,
            'turno' => $turno,
        ]);
    
        if (!$headquarter_id) {
            Log::warning('⚠ Usuario sede sin sede_id asignado', ['usuario_id' => $user->id]);
            return back()->withErrors('Este usuario no tiene una sede asignada.');
        }
    
        // Obtener productos
        $products = Product::where('estado', 0)
            ->where('category_id', 3)
            ->orderBy('nombre', 'asc')
            ->get();
    
        Log::debug('✅ Productos cargados', [
            'total' => $products->count(),
            'product_ids' => $products->pluck('id')->toArray()
        ]);
    
        // Cargar stock inicial
        $stockIniciales = StockInicial::where('headquarter_id', $headquarter_id)
            ->pluck('quantity', 'product_id')
            ->toArray();
    
        // ============ NUEVA LÓGICA: ENTRADAS Y SALIDAS ============
        $entradasPorProducto = [];
        $salidasPorProducto = [];
    
        // Obtener todos los movimientos del día
        $movementDetails = MovementDetail::with('movement')
            ->whereHas('movement', function ($q) use ($fecha, $turno, $headquarter_id) {
                $q->whereDate('date', $fecha)
                    ->where('estado', 0)
                    ->whereIn('tipo', [1, 2, 3, 4, 6]) // Todos los tipos
                    ->when(!is_null($turno), function($q) use ($turno) { return $q->where('turno', $turno); })
                    ->where(function ($sub) use ($headquarter_id) {
                        $sub->where('headquarter_id', $headquarter_id)
                            ->orWhere('headquarter_to_id', $headquarter_id);
                    });
            })
            ->where('estado', 0)
            ->get();
    
        foreach ($movementDetails as $detail) {
            $productId = $detail->product_id;
            $tipo = $detail->movement->tipo;
    
            // Inicializar arrays si no existen
            if (!isset($entradasPorProducto[$productId])) {
                $entradasPorProducto[$productId] = 0;
            }
            if (!isset($salidasPorProducto[$productId])) {
                $salidasPorProducto[$productId] = 0;
            }
    
            // === ENTRADAS (todo lo que suma) ===
            if ($tipo == 6 && $detail->movement->headquarter_id == $headquarter_id) {
                // Ingresos por producción
                $entradasPorProducto[$productId] += $detail->quantity;
            }
            
            if ($tipo == 2 && $detail->movement->headquarter_to_id == $headquarter_id) {
                // Traslados recibidos
                $entradasPorProducto[$productId] += $detail->quantity;
            }
            
            if ($tipo == 1 && $detail->transformado == 1 && $detail->movement->headquarter_id == $headquarter_id) {
                // Transformaciones - producto generado
                $entradasPorProducto[$productId] += $detail->quantity;
            }
    
            // === SALIDAS (todo lo que resta) ===
            if ($tipo == 3 && $detail->movement->headquarter_id == $headquarter_id) {
                // Mermas
                $salidasPorProducto[$productId] += $detail->quantity;
            }
            
            if ($tipo == 4 && $detail->movement->headquarter_id == $headquarter_id) {
                // Retoques
                $salidasPorProducto[$productId] += $detail->quantity;
            }
            
            if ($tipo == 2 && $detail->movement->headquarter_id == $headquarter_id) {
                // Traslados enviados
                $salidasPorProducto[$productId] += $detail->quantity;
            }
            
            if ($tipo == 1 && $detail->transformado == 0 && $detail->movement->headquarter_id == $headquarter_id) {
                // Transformaciones - producto base usado
                $salidasPorProducto[$productId] += $detail->quantity;
            }
        }
    
        // Calcular venta teórica simplificada
        $ventaTeoricaPorProducto = [];
        foreach ($products as $product) {
            $id = $product->id;
            
            $stockInicial = $stockIniciales[$id] ?? 0;
            $entradas = $entradasPorProducto[$id] ?? 0;
            $salidas = $salidasPorProducto[$id] ?? 0;
            
            // Fórmula simplificada: Stock Inicial + Entradas - Salidas = Stock Teórico
            // Por lo tanto: Venta Teórica = Stock Inicial + Entradas - Salidas - Stock Final
            // (Se calculará en el frontend cuando ingresen el Stock Final)
            
            $ventaTeoricaPorProducto[$id] = $stockInicial + $entradas - $salidas;
        }
    
        // Obtener ventas reales
        $ventasPorProducto = SaleDetail::whereHas('sale', function ($q) use ($fecha, $headquarter_id, $turno) {
            $q->whereRaw('DATE(fecha) = ?', [$fecha])
                ->where('headquarter_id', $headquarter_id)
                ->where('type_sale', 0)
                ->where('estado', 0)
                ->where('turno', $turno);
        })
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->toArray();
    
        Log::debug('📦 Entradas por producto:', $entradasPorProducto);
        Log::debug('📦 Salidas por producto:', $salidasPorProducto);
        Log::debug('📦 Ventas por producto:', $ventasPorProducto);
    
        $sedes = Headquarters::where('estado', 0)->get();
    
        return view('stock.create', compact(
            'products',
            'sedes',
            'turno',
            'headquarter_id',
            'fecha',
            'stockIniciales',
            'entradasPorProducto',
            'salidasPorProducto',
            'ventaTeoricaPorProducto',
            'ventasPorProducto'
        ));
    }

    public function stockInicial(Request $request)
    {
        $user = auth()->user();

        $fecha = now()->format('Y-m-d');
        $headquarter_id = $user->sede_id;

        Log::debug('📥 Entrada a stock.inicial', [
            'usuario_id' => $user->id,
            'sede_id' => $headquarter_id,
            'fecha' => $fecha,
        ]);

        $products = Product::where('estado', 0)
            ->where('category_id', 3)
            ->orderBy('name', 'asc')
            ->get();

        Log::debug('✅ Productos cargados para stock inicial', [
            'total' => $products->count(),
            'product_ids' => $products->pluck('id')->toArray()
        ]);

        return view('stock.inicial', compact('products', 'fecha', 'headquarter_id'));
    }

    public function guardar(Request $request)
    {
        $user = auth()->user();
        $headquarter_id = $user->sede_id;

        // Validar que se reciban los datos necesarios
        $request->validate([
            'quantity' => 'required|array',
            'quantity.*' => 'nullable|numeric|min:0',
            'headquarter_id' => 'required|numeric'
        ]);

        $stocks = $request->input('quantity');

        foreach ($stocks as $product_id => $stock_inicial) {
            if ($stock_inicial !== null && $stock_inicial !== '') {
                // Guarda o actualiza el stock inicial según tu modelo
                // Ejemplo usando un modelo Stock
                StockInicial::updateOrCreate(
                    [
                        'product_id' => $product_id,
                        'headquarter_id' => $headquarter_id,
                    ],
                    [
                        'quantity' => $stock_inicial,
                        'fecha' => now(),
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock inicial guardado correctamente.'
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user_id = $user->id;
            $turno = $user->turno;
            $headquarter_id = $user->sede_id;
            $fecha = now()->format('Y-m-d');
    
            $existingStock = Stock::where('user_id', $user_id)
                ->where('headquarter_id', $headquarter_id)
                ->where('turno', $turno)
                ->whereDate('fecha', $fecha)
                ->exists();
    
            if ($existingStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un paloteo registrado para este usuario, sede, turno y fecha: ' . $fecha
                ], 400);
            }
    
            // Validación básica - incluyendo entradas y salidas
            $request->validate([
                'product_id'      => 'required|array',
                'stock_inicial'   => 'required|array',
                'entradas'        => 'required|array',
                'salidas'         => 'required|array',
                'stock_final'     => 'required|array',
                'venta_teorica'   => 'required|array',
                'venta_real'      => 'required|array',
            ]);
    
            foreach ($request->product_id as $index => $productId) {
                Stock::updateOrCreate(
                    [
                        'headquarter_id' => $headquarter_id,
                        'product_id'     => $productId,
                        'fecha'          => $fecha,
                        'turno'          => $turno,
                    ],
                    [
                        'user_id'        => $user_id,
                        'stock_inicial'  => $request->stock_inicial[$index] ?? 0,
                        'entradas'       => $request->entradas[$index] ?? 0,
                        'salidas'        => $request->salidas[$index] ?? 0,
                        'stock_final'    => $request->stock_final[$index] ?? 0,
                        'venta_teorica'  => $request->venta_teorica[$index] ?? 0,
                        'venta_real'     => $request->venta_real[$index] ?? 0,
                        'encuadre'       => ($request->venta_teorica[$index] ?? 0) == ($request->venta_real[$index] ?? 0) ? 0 : 1,
                    ]
                );
    
                // Actualizar stock inicial para el próximo día
                StockInicial::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'headquarter_id' => $headquarter_id,
                    ],
                    [
                        'quantity' => $request->stock_final[$index] ?? 0,
                        'fecha' => $fecha,
                    ]
                );
            }
    
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cuadre de stock guardado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el cuadre de stock: ' . $e->getMessage()
            ], 500);
        }
    }
}
