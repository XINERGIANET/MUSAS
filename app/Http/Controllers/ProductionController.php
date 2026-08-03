<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\MovementDetail;
use App\Models\Presentation;
use App\Models\Product;
use App\Models\Headquarters;
use App\Models\Puesto;
use App\Models\Staff;
use App\Models\StockInicial;
use App\Models\Storage3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductionController extends Controller
{
    public function create()
    {
        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', 0)
            ->orderBy('nombre');

        if (auth()->user()->hasRole('adminSede')) {
            $sedes->where('id', '=', auth()->user()->headquarter->id);
        }

        $presentaciones = Presentation::where('estado', '=', 0)
            ->get();

        $sedes = $sedes->get();

        $productos = Product::with('presentation')
            ->where('category_id', 3)
            ->where('estado', 0)
            ->get(); // ← sin limitar los campos
        return view('production.create', compact('sedes', 'productos', 'presentaciones'));
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
                    "tipo" => 6,
                    'turno' => $turno,
                ]);

                foreach ($productos as $detail) {
                    $production->movementDetails()->create([
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                    ]);

                    // Stock
                    $storage = Storage3::firstOrNew([
                        'product_id' => $detail['product_id'],
                        'headquarter_id' => $headquarterId,
                    ]);

                    $storage->quantity = $storage->exists
                        ? $storage->quantity + $detail['quantity']
                        : $detail['quantity'];
                    $storage->save();

                    // Crear stock inicial si no existe
                    StockInicial::firstOrCreate([
                        'product_id'     => $detail['product_id'],
                        'headquarter_id' => $headquarterId,
                    ], [
                        'quantity' => 0
                    ]);
                }

                $productions[] = $production->id;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Producción registrada correctamente.',
                'productions' => $productions
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar producción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show() {}

    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $sede = $request->headquarter_id;
        $turno = $request->turno;

        $sedes = Headquarters::select('id', 'nombre')
            ->where('estado', '=', 0)
            ->orderBy('nombre')
            ->get();

        $consulta = Movement::with(['headquarter', 'movementDetails' => function ($query) {
            $query->where('estado', 0); // Solo detalles no eliminados
        }, 'movementDetails.product.productSede'])
            ->orderByDesc('date')
            ->where('tipo', '=', 6)
            ->where('estado', '=', 0) // Solo mostrar producciones no eliminadas
            ->orderByDesc('created_at')
            ->when($start_date, function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($sede, function ($query) use ($sede) {
                return $query->where('headquarter_id', $sede);
            })
            ->when($turno !== null && $turno !== '', function ($query) use ($turno) {
                return $query->where('turno', $turno);
            });

        $productions = $consulta->paginate(20)->appends(request()->query());

        $total = $consulta->get()->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $productSede = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first();

                $precio = $productSede ? $productSede->unit_price : $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });

        return view('production.index', compact('productions', 'total', 'sedes', 'turno'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Buscar el detalle específico por ID
            $detail = MovementDetail::with(['movement', 'product'])->findOrFail($id);

            // Verificar que el detalle no esté ya eliminado
            if ($detail->estado == 1) {
                return redirect()->route('production.index')
                    ->with('error', 'El detalle de producción ya está eliminado.');
            }

            // Verificar que es una producción normal (tipo 6)
            if ($detail->movement->tipo != 6) {
                return redirect()->route('production.index')
                    ->with('error', 'Solo se pueden eliminar detalles de producción normal.');
            }

            // Restar la cantidad del stock
            $storage = Storage3::where([
                'product_id' => $detail->product_id,
                'headquarter_id' => $detail->movement->headquarter_id,
            ])->first();

            if ($storage) {
                // Verificar que hay suficiente stock para restar
                if ($storage->quantity < $detail->quantity) {
                    DB::rollBack();
                    return redirect()->route('production.index')
                        ->with('error', 'No hay suficiente stock para eliminar este detalle. Producto: ' . $detail->product->nombre);
                }

                // Restar la cantidad del stock
                $storage->quantity -= $detail->quantity;
                $storage->save();
            }

            // Marcar solo el detalle como eliminado
            $detail->update(['estado' => 1]);

            DB::commit();

            return redirect()->route('production.index')
                ->with('success', 'Detalle de producción eliminado correctamente y stock actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('production.index')
                ->with('error', 'Error al eliminar el detalle: ' . $e->getMessage());
        }
    }

    public function pdf($beginDate, $endDate, $sede = null)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';

        $productions = Movement::with([
                'movementDetails' => function ($query) {
                    $query->where('estado', 0)
                        ->whereHas('product', function ($q) {
                            $q->where('estado', 0);
                        });
                },
                'movementDetails.product.productSede', 'headquarter'
            ])
            ->join('headquarters', 'movements.headquarter_id', '=', 'headquarters.id')
            ->orderBy('headquarters.nombre')
            ->orderBy('date')
            ->where('tipo', 6) //tipo producción
            ->where('movements.estado', 0) // Solo producciones no eliminadas
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when(isset($sede), function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            ->select('movements.*')
            ->get();

        $total = $productions->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $productSede = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first();
                $precio = $productSede ? $productSede->unit_price : $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });

        $pdf = Pdf::loadView('production.pdf', compact('productions', 'total'));
        return $pdf->download('Produccion.pdf');
    }

    public function pdfanticipos($beginDate, $endDate, $sede = null)
    {
        $start_date  = $beginDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';

        $productions = Movement::with([
                'movementDetails' => function ($query) {
                    $query->where('estado', 0)
                        ->whereHas('product', function ($q) {
                            $q->where('estado', 0);
                        });
                },
                'movementDetails.product.productSede', 'headquarter'
            ])
            ->join('headquarters', 'movements.headquarter_id', '=', 'headquarters.id')
            ->orderBy('headquarters.nombre')
            ->orderBy('date')
            ->whereIn('tipo', [6, 8, 9]) //tipo producción normal (6) y anticipada (8)
            ->where('movements.estado','=',0) // Solo producciones no eliminadas
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->when(isset($sede), function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            // ->orderBy('tipo', 'asc') // Ordenar por tipo: primero 6 (normales), luego 8 (anticipadas)
            // ->orderByDesc('date')
            // ->orderByDesc('created_at')
            ->select('movements.*')
            ->get();

        $total = $productions->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $productSede = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first();
                $precio = $productSede ? $productSede->unit_price : $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });

        $pdf = Pdf::loadView('production.pdfanticipos', compact('productions', 'total'));
        return $pdf->download('Produccion.pdf');
    }

    /**
     * Mostrar producción de la sede actual solo del día de hoy
     * Vista simplificada para acceso desde el menú de ventas
     */
    public function sedeHoy()
    {
        $userSede = auth()->user()->sede_id;
        $today = Carbon::today()->format('Y-m-d');

        // Consulta específica: solo la sede del usuario y solo hoy
        $consulta = Movement::with(['headquarter', 'movementDetails.product.productSede'])
            ->where('tipo', 6) // tipo 6 = producción
            ->where('estado', 0) // Solo producciones no eliminadas
            ->where('headquarter_id', $userSede)
            ->whereDate('date', $today)
            ->orderByDesc('created_at');

        $productions = $consulta->get();

        // Calcular total solo de la producción de hoy de esta sede
        $total = $productions->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $precioSede = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first();
                $precio = $precioSede ? $precioSede->unit_price : $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });

        $sedeFiltro = auth()->user()->headquarter->nombre;

        return view('production.dia', compact('productions', 'total', 'sedeFiltro', 'today'));
    }

    public function personalized(Request $request)
    {
        $products = Product::active()
            ->with('category')
            ->where('category_id', '3')
            ->get();

        $headquarters = Headquarters::active()
            ->get();

        $encargados = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->whereIn('puesto_id', [6, 7, 8])
            ->get();

        $puestos = Puesto::whereIn('id', [6, 7, 8])
            ->where('estado', 0) // asumiendo que tienes un campo estado
            ->get();  

        $title = "Producción para productos personalizados";

        return view('production.personalized', compact('products', 'headquarters', 'title', 'encargados', 'puestos'));
    }

    public function delivery(Request $request)
    {
        $products = Product::active()
            ->with('category')
            ->where('category_id', '3')
            ->get();

        $headquarters = Headquarters::active()
            ->get();

        $title = "Producción de productos para delivery";

        $encargados = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->whereIn('puesto_id', [6, 7, 8])
            ->get();

        $puestos = Puesto::whereIn('id', [6, 7, 8])
            ->where('estado', 0) // asumiendo que tienes un campo estado
            ->get();  

        return view('production.personalized', compact('products', 'headquarters', 'title', 'encargados', 'puestos'));
    }

    public function storeDelivery(Request $request)
    {
        $details = json_decode($request->input('products'), true);
        $request->merge(['details' => $details]);

        $validator = Validator::make($request->all(), [
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.headquarter_id' => 'required|exists:headquarters,id',
            'details.*.staff_id' => 'nullable|exists:staff,id',
            'details.*.turno' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $fecha = now(); // Usar fecha actual para producciones personalizadas

            // Agrupar por sede y turno
            // $grouped = collect($details)->groupBy(function($item) {
            //     return $item['headquarter_id'] . '_' . $item['turno'];
            // });

            $productions = [];

            foreach ($details as $detail) {
                // $keyParts = explode('_', $key);
                // $headquarterId = $keyParts[0];
                // $turno = $keyParts[1];

                // Crear producción personalizada por sede y turno
                $production = Movement::create([
                    'headquarter_id' => $detail['headquarter_id'],
                    'date' => $fecha,
                    'tipo' => 9, // Tipo 7 para producciones personalizadas
                    'turno' => $detail['turno'],
                    'estado' => 0
                ]);

                $production->movementDetails()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['price'],
                    'staff_id' => $detail['staff_id']
                ]);

                $productions[] = $production->id;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Producción delivery registrada correctamente.',
                'productions' => $productions
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar producción delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historico(Request $request)
    {
        $headquarters = Headquarters::active()->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $sede = $request->headquarter_id;
        $turno = $request->turno;
        $encargado = $request->staff_id;

        $encargados = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->whereIn('puesto_id', [6, 7, 8])
            ->get();

        $consulta = Movement::with(['headquarter', 'movementDetails.product', 'movementDetails.staff'])
            ->where('tipo', 8)
            ->where('estado', 0) // Solo mostrar producciones no eliminadas
            ->orderByDesc('created_at')
            ->when($start_date, function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($sede, function ($query) use ($sede) {
                return $query->where('headquarter_id', $sede);
            })
            ->when($encargado, function ($query) use ($encargado) {
                return $query->whereHas('movementDetails', function ($subQuery) use ($encargado) {
                    $subQuery->where('staff_id', $encargado);
                });
            })
            ->when($turno !== null && $turno !== '', function ($query) use ($turno) {
                return $query->where('turno', $turno);
            });

        $productions = $consulta->get();

        $title = "Histórico de producción para personalizadas";

        return view('production.historico', compact('headquarters', 'productions', 'title', 'encargados'));
    }

    public function historicoDelivery(Request $request)
    {
        $headquarters = Headquarters::active()->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $sede = $request->headquarter_id;
        $turno = $request->turno;
        $encargado = $request->staff_id;


        $encargados = Staff::select(['id', 'nombre'])
            ->where('estado', 0)
            ->whereIn('puesto_id', [6, 7, 8])
            ->get();

        $consulta = Movement::with(['headquarter', 'movementDetails.product'])
            ->where('tipo', 9)
            ->where('estado', 0) // Solo mostrar producciones no eliminadas
            ->orderByDesc('created_at')
            ->when($start_date, function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($sede, function ($query) use ($sede) {
                return $query->where('headquarter_id', $sede);
            })
            ->when($encargado, function ($query) use ($encargado) {
                return $query->whereHas('movementDetails', function ($subQuery) use ($encargado) {
                    $subQuery->where('staff_id', $encargado);
                });
            })
            ->when($turno !== null && $turno !== '', function ($query) use ($turno) {
                return $query->where('turno', $turno);
            });

        $productions = $consulta->get();

        $title = "Histórico de producción para delivery";

        return view('production.historico', compact('headquarters', 'productions', 'title', 'encargados'));
    }

    public function pdf_personalized($beginDate, $endDate, $sede = null, $turno = null, $tipo = 8)
    {
        $sede = $sede === 'null' ? null : $sede;
        $turno = $turno === 'null' ? null : $turno;

        $productions = Movement::with(['headquarter', 'movementDetails.product.productSede'])
            ->join('headquarters', 'movements.headquarter_id', '=', 'headquarters.id')
            ->orderBy('headquarters.nombre')
            ->orderBy('date')
            ->where('tipo', $tipo)
            ->where('movements.estado', 0)
            ->whereDate('date', '>=', $beginDate)
            ->whereDate('date', '<=', $endDate)
            ->when($sede, fn($q) => $q->where('headquarter_id', $sede))
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', $turno))
            ->select('movements.*')
            ->get();

        // Cálculo del total con prioridad a movementDetails->unit_price
        if ($tipo == 9) {
            // Delivery
            $total = $productions->sum(function ($prod) {
                return $prod->movementDetails->sum(function ($detail) use ($prod) {
                    // 1. Priorizar unit_price del detalle
                    if ($detail->unit_price !== null) {
                        $precio = $detail->unit_price;
                    } else {
                        // 2. Luego el precio del producto en la sede
                        $productSede = $detail->product->productSede
                            ->where('headquarter_id', $prod->headquarter_id)
                            ->first();
                        $precio = $productSede ? $productSede->unit_price : $detail->product->unit_price;
                    }
                    return $detail->quantity * $precio;
                });
            });
        } else {
            // Personalizada
            $total = $productions->sum(function ($prod) {
                return $prod->movementDetails->sum(function ($detail) {
                    $precio = $detail->unit_price ?? $detail->product->unit_price;
                    return $detail->quantity * $precio;
                });
            });
        }

        $pdf = Pdf::loadView('production.pdf_personalized', compact('productions', 'total'));
        return $pdf->download($tipo == 9 ? 'Produccion_Delivery.pdf' : 'Produccion_Personalizada.pdf');
    }


    public function pdf_summary($beginDate, $endDate, $sede = null, $turno = null, $tipo = 8)
    {
        $sede = $sede === 'null' ? null : $sede;
        $turno = $turno === 'null' ? null : $turno;

        $consulta = Movement::with(['headquarter', 'movementDetails.product'])
            ->where('tipo', $tipo)
            ->where('estado', 0)
            ->whereDate('date', '>=', $beginDate)
            ->whereDate('date', '<=', $endDate)
            ->when($sede, fn($q) => $q->where('headquarter_id', $sede))
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', $turno))
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $productions = $consulta->get();

        $resumen = collect();
        if ($productions->count() > 0) {

            $detalles = $productions->flatMap(function ($mov) {
                return $mov->movementDetails;
            });

            $resumen = $detalles
                ->where('estado', 0)
                ->groupBy('product_id')->map(function ($items) {
                    $producto = $items->first()->product;
                    if (!$producto) {
                        return null;
                    }

                    $cantidadTotal = $items->sum('quantity');
                    // Suma de (quantity * unit_price) de cada detalle
                    $subtotal = $items->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    });

                    return [
                        'producto' => $producto->nombre,
                        'cantidad_total' => $cantidadTotal,
                        'precio_unitario' => $producto->unit_price,
                        'subtotal' => $subtotal
                    ];
                })
                ->filter(); // Eliminar elementos null
        }

        $nombre_sede = optional(Headquarters::find($sede))->nombre;

        $total = $resumen->sum('subtotal');

        $filterInfo = [
            'startDate' => $beginDate,
            'endDate' => $endDate,
            'sede' => $nombre_sede,
            'turno' => $turno,
        ];



        $pdf = Pdf::loadView('production.pdf-resumen-anticipated', compact('resumen', 'total', 'filterInfo'));
        return $pdf->download($tipo == 8 ? 'Produccion_Resumen_Personalizada.pdf' : 'Produccion_Resumen_Delivery.pdf');
    }


    public function destroyDelivery($id)
    {
        DB::beginTransaction();

        try {
            $production = Movement::with('movementDetails.product')->findOrFail($id);

            // Verificar que es una producción personalizada
            if ($production->tipo != 9) {
                return redirect()->route('production.historicoDelivery')
                    ->with('error', 'Esta no es una producción personalizada.');
            }

            // Verificar que la producción no esté ya eliminada
            if ($production->estado == 1) {
                return redirect()->route('production.historicoDelivery')
                    ->with('error', 'La producción ya está eliminada.');
            }

            // NO restar stock para producciones personalizadas
            // Las producciones personalizadas no afectan el inventario

            // Marcar la producción como eliminada
            $production->update(['estado' => 1]);

            DB::commit();

            return redirect()->route('production.historicoDelivery')
                ->with('success', 'Producción personalizada eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('production.historicoDelivery')
                ->with('error', 'Error al eliminar la producción: ' . $e->getMessage());
        }
    }

    public function storePersonalized(Request $request)
    {
        $details = json_decode($request->input('products'), true);
        $request->merge(['details' => $details]);

        $validator = Validator::make($request->all(), [
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|numeric|min:0.01',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.headquarter_id' => 'required|exists:headquarters,id',
            'details.*.staff_id' => 'nullable|exists:staff,id',
            'details.*.turno' => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $fecha = now(); // Usar fecha actual para producciones personalizadas

            // Agrupar por sede y turno
            // $grouped = collect($details)->groupBy(function($item) {
            //     return $item['headquarter_id'] . '_' . $item['turno'];
            // });

            $productions = [];

            foreach ($details as $detail) {
                // $keyParts = explode('_', $key);
                // $headquarterId = $keyParts[0];
                // $turno = $keyParts[1];

                // Crear producción personalizada por sede y turno
                $production = Movement::create([
                    'headquarter_id' => $detail['headquarter_id'],
                    'date' => $fecha,
                    'tipo' => 8, // Tipo 7 para producciones personalizadas
                    'turno' => $detail['turno'],
                    'estado' => 0
                ]);

                $production->movementDetails()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['price'],
                    'staff_id' => $detail['staff_id']
                ]);


                $productions[] = $production->id;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Producción personalizada registrada correctamente.',
                'productions' => $productions
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => 'Error al registrar producción personalizada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyPersonalized($id)
    {
        DB::beginTransaction();

        try {
            $production = Movement::with('movementDetails.product')->findOrFail($id);

            // Verificar que es una producción personalizada
            if ($production->tipo != 8) {
                return redirect()->route('production.historico')
                    ->with('error', 'Esta no es una producción personalizada.');
            }

            // Verificar que la producción no esté ya eliminada
            if ($production->estado == 1) {
                return redirect()->route('production.historico')
                    ->with('error', 'La producción ya está eliminada.');
            }

            // NO restar stock para producciones personalizadas
            // Las producciones personalizadas no afectan el inventario

            // Marcar la producción como eliminada
            $production->update(['estado' => 1]);

            DB::commit();

            return redirect()->route('production.historico')
                ->with('success', 'Producción personalizada eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('production.historico')
                ->with('error', 'Error al eliminar la producción: ' . $e->getMessage());
        }
    }

    /**
     * Genera un PDF resumen de producción agrupado por producto.
     */
    /* public function pdfResumen($startDate, $endDate, $sede = null, $turno = null)
    {
        $start_date = $startDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';

        $productions = Movement::with(['movementDetails.product.productSede'])
            ->whereIn('tipo', [6, 8, 9])
            ->where('estado', 0)
            ->where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->when($sede, function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            ->when($turno !== null && $turno !== '', function ($query) use ($turno) {
                $query->where('turno', $turno);
            })
            ->get();

        $resumen = [];
        foreach ($productions as $production) {
            foreach ($production->movementDetails as $detail) {
                $product = $detail->product;
                $productSede = $product->productSede->where('headquarter_id', $production->headquarter_id)->first();
                $precio = $productSede ? $productSede->unit_price : $product->unit_price;
                $pid = $product->id;
                if (!isset($resumen[$pid])) {
                    $resumen[$pid] = [
                        'nombre' => $product->nombre,
                        'cantidad' => 0,
                        'precio' => $precio,
                        'subtotal' => 0,
                    ];
                }
                $resumen[$pid]['cantidad'] += $detail->quantity;
                $resumen[$pid]['subtotal'] += $detail->quantity * $precio;
            }
        }

        $filterInfo = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'headquarter' => $sede ? optional(\App\Models\Headquarters::find($sede))->nombre : null,
            'turno' => $turno,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('production.pdf-resumen', compact('resumen', 'filterInfo'));
        return $pdf->download('ResumenProduccion.pdf');
    } */

    public function pdfResumen($startDate, $endDate, $sede = null, $turno = null)
    {
        $start_date = $startDate . ' 00:00:00';
        $end_date = $endDate . ' 23:59:59';
    
        $productions = Movement::with([
                'movementDetails' => function ($query) {
                    $query->where('estado', 0)
                        ->whereHas('product', function ($q) {
                            $q->where('estado', 0);
                        });
                },
                'movementDetails.product.productSede'
            ])
            ->whereIn('tipo', [6, 8, 9])
            ->where('estado', 0)
            ->where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->when($sede, function ($query) use ($sede) {
                $query->where('headquarter_id', $sede);
            })
            ->when($turno !== null && $turno !== '', function ($query) use ($turno) {
                $query->where('turno', $turno);
            })
            ->get();
    
        $resumen = [
            'normal' => [],
            'anticipada' => [],
            'delivery' => [],
        ];
    
        foreach ($productions as $production) {
            $tipo = $production->tipo == 6 ? 'normal' : ($production->tipo == 8 ? 'anticipada' : 'delivery');
            foreach ($production->movementDetails as $detail) {
                $product = $detail->product;
                // Para anticipada (8) y delivery (9) usar el unit_price del detalle
                if ($production->tipo == 8 || $production->tipo == 9) {
                    $precio = $detail->unit_price;
                } else {
                    $productSede = $product->productSede->where('headquarter_id', $production->headquarter_id)->first();
                    $precio = $productSede ? $productSede->unit_price : $product->unit_price;
                }
                $pid = $product->id;
                if (!isset($resumen[$tipo][$pid])) {
                    $resumen[$tipo][$pid] = [
                        'nombre' => $product->nombre,
                        'cantidad' => 0,
                        'precio' => $precio,
                        'subtotal' => 0,
                    ];
                }
                $resumen[$tipo][$pid]['cantidad'] += $detail->quantity;
                $resumen[$tipo][$pid]['subtotal'] += $detail->quantity * $precio;
            }
        }
    
        $filterInfo = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'headquarter' => $sede ? optional(\App\Models\Headquarters::find($sede))->nombre : null,
            'turno' => $turno,
        ];
    
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('production.pdf-resumen', compact('resumen', 'filterInfo'));
        return $pdf->download('ResumenProduccion.pdf');
    }
}
