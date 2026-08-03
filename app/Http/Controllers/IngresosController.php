<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Headquarters;
use App\Models\Movement;
use App\Models\Product;
use App\Models\StockInicial;
use App\Models\Storage2;
use App\Models\Storage3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Psr\Log\NullLogger;

class IngresosController extends Controller
{
    //
    public function index(Request $request)
    {
        $categoria = in_array($request->categoria, ['insumos','industrializados'])
            ? $request->categoria
            : 'insumos';

        $map = ['insumos' => 4, 'industrializados' => 2];
        $cat_id = $map[$categoria];

        $start_date = $request->start_date;
        $end_date   = $request->end_date;
        $sede       = $request->headquarter_id;

        $sedes = Headquarters::select('id','nombre')
            ->where('estado', 0)
            ->orderBy('nombre')
            ->get();

        $consulta = Movement::with(['headquarter','movementDetails.product'])
            ->where('tipo', 7) 
            ->when($start_date, fn($q) => $q->whereDate('date','>=',$start_date))
            ->when($end_date,   fn($q) => $q->whereDate('date','<=',$end_date))
            ->when(isset($sede),fn($q) => $q->where('headquarter_id',$sede))
            ->whereHas('movementDetails.product', fn($q) => $q->where('category_id', $cat_id))
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $productions = $consulta->paginate(25)->appends(['categoria' => $categoria]);

        $total = $consulta->get()->sum(function ($mov) {
            return $mov->movementDetails->sum(fn($d) => $d->quantity * $d->product->unit_price);
        });

        return view('ingresos.index', compact('productions','total','sedes','categoria'));
    }

    // Mostrar el formulario para crear una nueva sede
    public function create(Request $request)
    {   
        $categoria = $request->categoria;
        $cat_id=-1;
        switch ($categoria){
            case "insumos":
                $cat_id = 4;
                break;
            case "industrializados":
                $cat_id = 2;
                break;

        }

        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', '=', 0)
            ->orderBy('nombre')
            ->get();
        $productos = Product::with('presentation')
            ->where('category_id', $cat_id)
            ->where('estado', 0)
            ->get(); // ← sin limitar los campos
        $categorias = Category::where('id', $cat_id)->where('estado', 0)->get();

        $stocks = Storage2::where('estado', 0)
            ->whereHas('product', function ($query) {
                $query->whereIn('category_id', [2, 4])
                    ->where('estado', 0);
            })
            ->with('product') // para evitar consultas N+1 en la vista
            ->get();
        return view('ingresos.create', compact('sedes', 'productos', 'categorias', 'stocks'));
    }


    public function store(Request $request)
    {
        $details = json_decode($request->input('products'), true);
        $request->merge(['details' => $details]);

        $validator = Validator::make($request->all(), [
            'turno' => 'required|integer|in:0,1',
            'client_datetime' => 'required|date',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.headquarter_id' => 'required|exists:headquarters,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $fecha = $request->input('client_datetime');
            $turno = $request->input('turno');

            // Agrupar por sede
            $grouped = collect($details)->groupBy('headquarter_id');
            $productions = [];

            foreach ($grouped as $headquarterId => $productos) {
                // Crear producción por sede
                $production = Movement::create([
                    'headquarter_id' => $headquarterId,
                    'date' => $fecha,
                    'turno' => $turno,
                    'tipo' => 7, //tipo ingreso
                ]);

                foreach ($productos as $detail) {
                    $production->movementDetails()->create([
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                    ]);

                    // Obtener el producto para determinar la categoría
                    $product = Product::find($detail['product_id']);
                    
                    // Tanto insumos (categoría 4) como productos industrializados (categoría 2) van a Storage2
                    $storage = Storage2::where('product_id', $detail['product_id'])->first();
                    
                    if ($storage) {
                        // Verificar si hay suficiente stock
                        if ($storage->quantity < $detail['quantity']) {
                            throw new \Exception("Stock insuficiente para el producto '{$product->nombre}'. Stock disponible: {$storage->quantity}, solicitado: {$detail['quantity']}");
                        }
                        $storage->quantity = $storage->quantity - $detail['quantity'];
                        $storage->save();
                    } else {
                        // Si no existe registro, no se puede hacer salida
                        throw new \Exception("No existe stock disponible para el producto '{$product->nombre}'");
                    }

                    // Si es producto industrializado (categoría 2), también aumentar en Storage3
                    if ($product->category_id == 2) {
                        $storage3 = Storage3::where('product_id', $detail['product_id'])
                            ->where('headquarter_id', $headquarterId)
                            ->first();
                        
                        if ($storage3) {
                            // Si existe, aumentar la cantidad
                            $storage3->quantity = $storage3->quantity + $detail['quantity'];
                            $storage3->save();
                        } else {
                            // Si no existe, crear nuevo registro
                            Storage3::create([
                                'product_id' => $detail['product_id'],
                                'headquarter_id' => $headquarterId,
                                'quantity' => $detail['quantity']
                            ]);
                        }
                    }
                }

                $productions[] = $production->id;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Ingresos registrados correctamente.',
                'productions' => $productions
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar ingresos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pdf($beginDate, $endDate, $categoria = null)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';


        $cat_id=-1;
        switch ($categoria){
            case "insumos":
                $cat_id = 4;
                break;
            case "industrializados":
                $cat_id = 2;
                break;

        }

        $isAdmin = auth()->user()->hasRole('admin'); 
        $sede = null;
        if (!$isAdmin) {
            // Si no es admin, busca por sede
            $sede = auth()->user()->headquarter_id;
        }

        $productions = Movement::with(['headquarter', 'movementDetails.product'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when($sede, function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            ->whereHas('movementDetails.product', function ($q) use ($cat_id) {
                $q->where('category_id', $cat_id);
            })
            ->get();

        $pdf = Pdf::loadView('ingresos.pdf', compact('productions'));
        return $pdf->download('Ingresos.pdf');
    }

    public function pdfResumen(Request $request)
    {
        $categoria = $request->categoria;
        $cat_id = -1;
        switch ($categoria) {
            case "insumos":
                $cat_id = 4;
                break;
            case "industrializados":
                $cat_id = 2;
                break;
        }

        $startDate = $request->start_date ?: now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?: now()->endOfYear()->format('Y-m-d');
        $sede = $request->headquarter_id;

        $consulta = Movement::with(['headquarter', 'movementDetails.product'])
            ->where('tipo', 7)
            ->whereBetween('date', [$startDate, $endDate])
            ->when($sede, fn($query) => $query->where('headquarter_id', $sede))
            ->whereHas('movementDetails.product', fn($q) => $q->where('category_id', $cat_id));

        $movements = $consulta->get();

        $resumen = [];

        foreach ($movements as $movement) {
            foreach ($movement->movementDetails as $detail) {
                $producto = $detail->product;
                if ($producto->category_id != $cat_id) continue;

                $id = $producto->id;

                if (!isset($resumen[$id])) {
                    $resumen[$id] = [
                        'producto' => $producto->nombre,
                        'cantidad_total' => 0,
                        'precio_unitario' => $producto->unit_price,
                        'subtotal' => 0,
                    ];
                }

                $resumen[$id]['cantidad_total'] += $detail->quantity;
                $resumen[$id]['subtotal'] = $resumen[$id]['cantidad_total'] * $producto->unit_price;
            }
        }

        $resumen = collect($resumen)->values();
        $total = $resumen->sum('subtotal');

        $sedeNombre = null;
        if ($sede) {
            $sedeObj = Headquarters::find($sede);
            $sedeNombre = $sedeObj ? $sedeObj->nombre : null;
        }

        $filterInfo = [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $pdf = Pdf::loadView('ingresos.pdf-resumen', compact('resumen', 'total', 'filterInfo'));
        return $pdf->download('resumen-ingresos.pdf');
    }
}
