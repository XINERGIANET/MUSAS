<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Product;
use App\Models\Puesto;
use App\Models\Staff;
use App\Models\Storage2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsumptionController extends Controller
{

    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search = $request->search;
        $staff_search = $request->staff_search;
        $area = $request->area;

        $consulta = Consumption::with(['product' => function ($query) {
                $query->select('id', 'nombre', 'unit_price');
            }, 'staff'])
            ->where('estado', 0)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                });
            })
            ->when($staff_search, function ($query) use ($staff_search) {
                $query->where('staff_id', $staff_search);
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('date', '<=', $end_date);
            })
            ->when($area, function ($query) use ($area) {
                $query->whereHas('staff', function ($q) use ($area) {
                    $q->where('area', $area);
                });
            });

        $consumptions = $consulta->orderBy('date', 'desc')
            ->paginate(10)
            ->appends(request()->query());

        $total = $consulta->get()->sum(function ($consumption) {
            return $consumption->quantity * $consumption->product->unit_price;
        });

        $staff = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->get();

        return view('consumption.index', compact('consumptions', 'total', 'staff'));
    }



    public function create()
    {
        $productos = Storage2::where('estado', 0)
            ->whereHas('product', function ($query) {
                $query->whereIn('category_id', [1, 4])
                    ->where('estado', 0);
            })
            ->with('product') // para evitar consultas N+1 en la vista
            ->get();

        $empleadosPanaderia = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->where('puesto_id', 6) // Panadería
            ->get();

        $empleadosPasteleria = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->where('puesto_id', 7) // Pastelería
            ->get();

        $empleadosCocina = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->where('puesto_id', 8) // Cocina
            ->get();

        $puestos = Puesto::whereIn('id', [6, 7, 8])
            ->where('estado', 0) // asumiendo que tienes un campo estado
            ->get();

        return view('consumption.create', compact(
            'productos',
            'empleadosPanaderia',
            'empleadosPasteleria',
            'empleadosCocina',
            'puestos'
        ));
    }

    public function store(Request $request)
    {
        // Decodificar el JSON enviado como string
        $consumptions = json_decode($request->input('consumptions'), true);
        $date = $request->input('date');

        // Validar cada producto
        $validated = Validator::make([
            'consumptions' => $consumptions,
            'date' => $date
        ], [
            'date' => 'required|date',
            'consumptions' => 'required|array',
            'consumptions.*.product_id' => 'required|exists:products,id',
            'consumptions.*.quantity' => 'required|numeric|min:0.01',
            'consumptions.*.encargado' => 'required|exists:staff,id',
            'consumptions.*.area' => 'required|string',
        ])->validate();

        DB::transaction(function () use ($validated) {
            foreach ($validated['consumptions'] as $consumptionData) {
                // 1. Crear registro de consumo
                Consumption::create([
                    'product_id' => $consumptionData['product_id'],
                    'quantity' => $consumptionData['quantity'],
                    'staff_id' =>  $consumptionData['encargado'],
                    'area' =>  $consumptionData['area'],
                    'date' => $validated['date'],
                ]);

                // 2. Descontar stock individual
                $storage = Storage2::where('product_id', $consumptionData['product_id'])->first();
                if ($storage) {
                    $storage->quantity -= $consumptionData['quantity'];
                    $storage->save();
                }
            }
        });

        return response()->json(['success' => 'Consumos registrados correctamente']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'quantity' => 'required|numeric|min:0.01',
            'area' => 'required|string|max:20',
        ]);

        $consumption = Consumption::findOrFail($id);
        $oldQuantity = $consumption->quantity;
        $newQuantity = $request->quantity;

        // Buscar el stock actual
        $storage = Storage2::where('product_id', $consumption->product_id)->first();
        if (!$storage) {
            return redirect()->back()->with('error', 'No se encontró stock disponible para el producto.');
        }

        $difference = $newQuantity - $oldQuantity;

        // Verificar si hay stock suficiente al aumentar el consumo
        if ($difference > 0 && $storage->quantity < $difference) {
            return redirect()->back()->with('error', 'Stock insuficiente para aumentar la cantidad.');
        }

        DB::transaction(function () use ($consumption, $request, $storage, $difference, $newQuantity) {
            // Ajustar el stock
            if ($difference > 0) {
                $storage->quantity -= $difference;
            } elseif ($difference < 0) {
                $storage->quantity += abs($difference);
            }

            $storage->save();

            // Actualizar consumo
            $consumption->update([
                'quantity' => $newQuantity,
                'staff_id' => $request->staff_id,
                'area' => $request->area,
            ]);
        });

        return redirect()->back()->with('success', 'Consumo actualizado correctamente.');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $consumption = Consumption::findOrFail($id);

            // Buscar el registro de storage2 correspondiente
            $storage = Storage2::where('product_id', $consumption->product_id)->first();

            if ($storage) {
                // Devolver la cantidad al stock
                $storage->quantity += $consumption->quantity;
                $storage->save();
            }

            // Cambiar estado a 1 (eliminado)
            $consumption->estado = 1;
            $consumption->save();

            DB::commit();

            return redirect()->back()->with('success', 'Consumo eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar consumo.'], 500);
        }
    }


    // Para búsqueda de materias primas
    // public function searchRawMaterials(Request $request)
    // {
    //     $query = $request->get('query');

    //     $materials = Storage::where('quantity', '>', 0)
    //         ->where('nombre', 'like', "%$query%")
    //         ->limit(10)
    //         ->get(['id', 'nombre', 'quantity', 'unit']);

    //     return response()->json($materials);
    // }

/*     public function pdf($beginDate, $endDate, $merma)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';

        $consumptions = Consumption::with(['product' => function ($query) {
            $query->select('id', 'nombre', 'unit_price');
        }])
            ->when($merma == 1, function ($query)  use ($merma) {
                $query->where('merma', $merma);
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('date', '<=', $end_date);
            })
            ->orderBy('date', 'desc')
            ->get();

        $pdf = Pdf::loadView('consumption.pdf', compact('consumptions'));
        return $pdf->download('Consumo.pdf');
    } */

    public function pdf(Request $request)
    {
        // Obtener parámetros del request - mismo patrón que pdfResumen
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $staff_search = $request->get('staff_search');
        $merma = $request->get('merma');

        // Si no hay fechas, usar año actual por defecto
        if (empty($startDate)) {
            $startDate = now()->startOfYear()->format('Y-m-d');
        }
        if (empty($endDate)) {
            $endDate = now()->endOfYear()->format('Y-m-d');
        }

        $consulta = Consumption::with(['product' => function ($query) {
            $query->select('id', 'nombre', 'unit_price');
        }, 'staff'])
            ->where('estado', 0) // Solo consumos activos
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                });
            })
            ->when($staff_search, function ($query) use ($staff_search) {
                $query->where('staff_id', $staff_search);
            })
            ->when(isset($merma) && $merma == 1, function ($query) use ($merma) {
                $query->where('merma', $merma);
            })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $consumptions = $consulta->orderBy('date', 'desc')->get();

        $total = $consumptions->sum(function ($consumption) {
            return $consumption->quantity * $consumption->product->unit_price;
        });

        // Obtener nombre del encargado si hay filtro
        $staffName = null;
        if ($staff_search) {
            $staff = Staff::find($staff_search);
            $staffName = $staff ? $staff->nombre : null;
        }

        // Datos adicionales para mostrar en el PDF
        $filterInfo = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'staff_search' => $staff_search,
            'staff_name' => $staffName,
            'merma' => $merma
        ];

        $pdf = Pdf::loadView('consumption.pdf', compact('consumptions', 'total', 'filterInfo'));
        
        return $pdf->download('reporte-consumos.pdf');
    }

    public function pdfResumen(Request $request)
    {
        // Obtener parámetros del request - tanto GET como query
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $staff_search = $request->get('staff_search');
        $merma = $request->get('merma');

        // Si no hay fechas, usar año actual por defecto
        if (empty($startDate)) {
            $startDate = now()->startOfYear()->format('Y-m-d');
        }
        if (empty($endDate)) {
            $endDate = now()->endOfYear()->format('Y-m-d');
        }

        $consulta = Consumption::with(['product' => function ($query) {
            $query->select('id', 'nombre', 'unit_price');
        }, 'staff'])
            ->where('estado', 0) // Solo consumos activos
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                });
            })
            ->when($staff_search, function ($query) use ($staff_search) {
                $query->where('staff_id', $staff_search);
            })
            ->when(isset($merma) && $merma == 1, function ($query) use ($merma) {
                $query->where('merma', $merma);
            })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $consumptions = $consulta->get();

        // Agrupar por producto y sumar cantidades
        $resumen = collect();
        if ($consumptions->count() > 0) {
            $resumen = $consumptions->groupBy('product_id')->map(function ($items) {
                $producto = $items->first()->product;
                if (!$producto) {
                    return null;
                }
                $cantidadTotal = $items->sum('quantity');
                $subtotal = $cantidadTotal * $producto->unit_price;
                
                return [
                    'producto' => $producto->nombre,
                    'cantidad_total' => $cantidadTotal,
                    'precio_unitario' => $producto->unit_price,
                    'subtotal' => $subtotal
                ];
            })->filter(); // Eliminar elementos null
        }

        $total = $resumen->sum('subtotal');

        // Datos adicionales para mostrar en el PDF
        $staffName = null;
        if ($staff_search) {
            $staff = Staff::find($staff_search);
            $staffName = $staff ? $staff->nombre : null;
        }

        $filterInfo = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'staff_search' => $staff_search,
            'staff_name' => $staffName,
            'merma' => $merma
        ];

        $pdf = Pdf::loadView('consumption.pdf-resumen', compact('resumen', 'total', 'filterInfo'));
        
        return $pdf->download('resumen-consumos.pdf');
    }

    public function pdfAreas(Request $request){
        // Obtener filtros
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $staff_search = $request->get('staff_search');
        $area = $request->get('area');

        // Si no hay fechas, usar año actual por defecto
        if (empty($startDate)) {
            $startDate = now()->startOfYear()->format('Y-m-d');
        }
        if (empty($endDate)) {
            $endDate = now()->endOfYear()->format('Y-m-d');
        }

        $consulta = Consumption::with(['product' => function ($query) {
            $query->select('id', 'nombre');
        }, 'staff'])
            ->where('estado', 0)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                });
            })
            ->when($staff_search, function ($query) use ($staff_search) {
                $query->where('staff_id', $staff_search);
            })
            ->when($area, function ($query) use ($area) {
                $query->where('area', $area);
            })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        $consumptions = $consulta->orderBy('area')->orderBy('staff_id')->orderBy('product_id')->get();

        // Agrupar por área, trabajador y producto
        $resumen = collect();
        if ($consumptions->count() > 0) {
            $resumen = $consumptions->groupBy(['area', 'staff_id', 'product_id']);
        }

        // Datos adicionales para mostrar en el PDF
        $filterInfo = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'staff_search' => $staff_search,
            'area' => $area
        ];

        $pdf = Pdf::loadView('consumption.pdf-areas', compact('resumen', 'filterInfo'));
        return $pdf->download('area-consumos.pdf');
    }
}
