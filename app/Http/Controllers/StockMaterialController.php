<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Headquarters;
use App\Models\Purchase;
use App\Models\MovementDetail;
use App\Models\Movement;
use App\Models\PurchaseDetail;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Product;
use App\Models\ProductionDetail;
use App\Models\Stock;
use App\Models\StockInicial;
use App\Models\Storage3;
use App\Models\Storage2;
use App\Models\SaleDetail;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Pdf as WriterPdf;
use Psy\Command\WhereamiCommand;

class StockMaterialController extends Controller
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

        $start_date = $request->start_date;
        $end_date   = $request->end_date;

        // Consulta principal con los JOINs
        $stock = Stock::with(['product'])
            ->when($start_date, fn($q) => $q->whereDate('fecha', '>=', $start_date))
            ->when($end_date,   fn($q) => $q->whereDate('fecha', '<=', $end_date))
            ->whereNull('venta_real')
            ->whereNull('turno')
            ->orderBy('fecha', 'desc')
            ->paginate(30);

        return view('stockMaterial.index', compact('stock', 'headquarters'));
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
            // ->when($end_date, function ($query) use ($end_date) {
            //     $query->whereDate('fecha', '<=', $end_date);
            // })
            ->when($sede, fn($q) => $q->where('headquarter_id', $sede))
            ->when($turno !== null, fn($q) => $q->where('turno', $turno))
            ->get();

        $user = auth()->user()->email;

        $nombre_sede = optional(Headquarters::find($sede))->nombre;

        $filterInfo = [
            'startDate' => $beginDate,
            //'endDate' => $endDate,
            'sede' => $nombre_sede,
            'turno' => $turno,
        ];

        $pdf = Pdf::loadView('stockMaterial.pdf', compact('stock', 'user', 'filterInfo'));
        return $pdf->download('Stock final.pdf');
    }

    public function pdfGeneral($beginDate, $endDate)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date    = $endDate . ' 23:59:59'; 

        $stock = Stock::with('product')
            ->whereBetween('fecha', [$start_date, $end_date]) 
            ->whereNull('headquarter_id') 
            ->whereNull('venta_real') 
            ->whereNull('turno') 
            ->whereNull('user_id') 
            ->select('product_id', 'stock_inicial', 'stock_final', 'venta_teorica', 'encuadre', 'fecha')
            ->get();

        $stock = $stock->sortBy(function ($item) {
            return $item->product->nombre; 
        });

        // Obtener los ingresos por producto (entradas)
        $ingresosPorProducto = PurchaseDetail::whereHas('purchase', function ($q) use ($start_date, $end_date) {
            $q->when($start_date, fn($qq) => $qq->whereDate('created_at','>=',$start_date))
                ->when($end_date, fn($qq) => $qq->whereDate('created_at','<=',$end_date))
                ->where('estado', 0);
        })
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->toArray();

        // Obtener los consumos (salidas)
        $consumos = Consumption::when($start_date, fn($qq) => $qq->whereDate('created_at','>=',$start_date))
            ->when($end_date, fn($qq) => $qq->whereDate('created_at','<=',$end_date))
            ->where('estado', 0)
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->map(fn($q) => -$q) // Restar consumos (salidas)
            ->toArray();

        $total = 0;
        foreach ($stock as $item) {
            $productId = $item->product_id;
            $item->entrada = $ingresosPorProducto[$productId] ?? 0; 
            $item->venta_teorica = $item->venta_teorica ?? 0; 
            $item->subtotal = $consumos[$productId] ?? 0; 
            $total += $item->subtotal;
        }

        $user = auth()->user()->email;

        $filterInfo = [
            'startDate' => $beginDate,
            'endDate' => $endDate,
        ];

        $pdf = Pdf::loadView('stockMaterial.pdfGeneral', compact('stock', 'user', 'filterInfo', 'total'));
        return $pdf->download('Reporte_paloteo.pdf');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        //$fecha = now()->format('Y-m-d');
        $turno = $user->turno;
        $headquarter_id = $user->sede_id;
        $start_date = $request->start_date ?? now()->format('Y-m-d');
        $end_date = $request->end_date ?? now()->format('Y-m-d');

        // 1. Productos activos de categoría 1 o 4
        $products = Product::where('estado', 0)
            ->whereIn('category_id', [1, 4])
            ->get();

        // 2. Stock inicial por sede
        $stockIniciales = StockInicial::where('headquarter_id', null) //sin sede
            ->pluck('quantity', 'product_id')
            ->toArray();

        // 3. Ingresos por compras (PurchaseDetail)
        $ingresosCompraPorProducto = PurchaseDetail::whereHas('purchase', function ($q) use ($start_date, $end_date) {
            $q->when($start_date, fn($qq) => $qq->whereDate('created_at','>=',$start_date))
            ->when($end_date,   fn($qq) => $qq->whereDate('created_at','<=',$end_date))
            ->where('estado', 0);
        })
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->toArray();

        // Movimientos tipo 7 (restan stock)
        $movimientosTipo7 = MovementDetail::whereHas('movement',function ($q) use ($start_date, $end_date) {
            $q->when($start_date, fn($qq) => $qq->whereDate('created_at','>=',$start_date))
            ->when($end_date,   fn($qq) => $qq->whereDate('created_at','<=',$end_date))
                ->where('estado', 0)
                ->where('tipo', 7);
        })
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->map(fn($q) => -$q) // convertir a negativo
            ->toArray();

        // Consumptions como respaldo si no hay movimientos
        $consumos = Consumption::when($start_date, fn($qq) => $qq->whereDate('created_at','>=',$start_date))
            ->when($end_date,   fn($qq) => $qq->whereDate('created_at','<=',$end_date))
            ->where('estado', 0)
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->map(fn($q) => -$q) // también restan
            ->toArray();

        // Combinar ambos: movimientos tienen prioridad
        $movimientosPorProducto = $consumos;
        foreach ($movimientosTipo7 as $productId => $cantidad) {
            $movimientosPorProducto[$productId] = $cantidad;
        }

        // 7. Venta teórica = SI + ingreso compra + ingreso gasto + movimientos tipo 7
        $stockTeoricoPorProducto = [];

        foreach ($products as $product) {
            $id = $product->id;
            $si = $stockIniciales[$id] ?? 0;
            $compra = $ingresosCompraPorProducto[$id] ?? 0;
            $gasto = 0; // $ingresosGastoPorProducto[$id] ?? 0;
            $movimiento = $movimientosPorProducto[$id] ?? 0;

            $stockTeoricoPorProducto[$id] = $si + $compra + $gasto + $movimiento;
        }

        // 9. Devolver vista con todo lo necesario
        return view('stockMaterial.create', compact(
            'products',
            'headquarter_id',
            'stockIniciales',
            'movimientosPorProducto',
            'stockTeoricoPorProducto',
            'ingresosCompraPorProducto',
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
            ->get();

        Log::debug('✅ Productos cargados para stock inicial', [
            'total' => $products->count(),
            'product_ids' => $products->pluck('id')->toArray()
        ]);

        return view('stockMaterial.inicial', compact('products', 'fecha', 'headquarter_id'));
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
            $fecha = now()->format('Y-m-d');

            // Validación básica (ajusta según tus necesidades)
            $request->validate([
                'product_id'      => 'required|array',
                'stock_inicial'   => 'required|array',
                'stock_final'     => 'required|array',
                'venta_teorica'      => 'required|array',
            ]);

            foreach ($request->product_id as $index => $productId) {
                Stock::updateOrCreate(
                    [
                        'headquarter_id' => null,
                        'product_id'     => $productId,
                        'fecha'          => $fecha,
                        'turno'          => null,
                    ],
                    [
                        'stock_inicial'  => $request->stock_inicial[$index] ?? 0,
                        'stock_final'    => $request->stock_final[$index] ?? 0,
                        'venta_teorica'  => $request->venta_teorica[$index] ?? 0,
                        'venta_real'     => null,
                        'encuadre'       => ($request->venta_teorica[$index] ?? 0) == ($request->stock_final[$index] ?? 0) ? 0 : 1, //cuadre entre stock final (ingresado) y venta teorica (stock teorico calculado)
                    ]
                );

                StockInicial::updateOrCreate(
                    [
                        'product_id' => $productId,
                        'headquarter_id' => null,
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
                'message' => 'Paloteo guardado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el paloteo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stockAlmacen(Request $request, $categoryId)
    {
        $search = $request->input('buscar');

        // Filtrar productos por la categoría dinámica recibida en la URL
        $filteredProducts = Product::where('estado', 0)
            ->where('category_id', $categoryId)
            ->when($search, fn($q) => $q->where('nombre', 'like', '%' . $search . '%'))
            ->orderBy('nombre', 'asc')
            ->get();

        // Obtener todas las sedes disponibles (solo aquellas activas)
        $headquarters = Headquarters::where('estado', 0)->get();

        // Pasar los productos y las sedes a la vista
        return view('stock.stockInicial', [
            'products' => $filteredProducts,
            'headquarters' => $headquarters,
            'categoryId' => $categoryId
        ]);
    }

    public function stockAlmacenStore(Request $request, $categoryId)
    {
        $productQuantities = $request->input('quantity');

        if (empty($productQuantities)) {
            return response()->json(['success' => false, 'message' => 'No se enviaron cantidades válidas.']);
        }

        foreach ($productQuantities as $productId => $quantity) {
            if ($quantity > 0) {
                $storage = StockInicial::where('product_id', $productId)
                ->where('headquarter_id',null)->first();

                if ($storage) {
                    $storage->quantity = $quantity;
                    $storage->save();
                } else {
                    StockInicial::create([
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'headquarter_id' => null,
                    ]);
                }
            }
        }

        if ($categoryId == 1) {
            $redirectUrl = route('storage1.index'); 
        } elseif ($categoryId == 4) {
            $redirectUrl = route('storageInsumo.index'); 
        } else {
            $redirectUrl = route('storage2.index'); 
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock inicial guardado correctamente.',
            'redirectUrl' => $redirectUrl,  
        ]);
    }
}
