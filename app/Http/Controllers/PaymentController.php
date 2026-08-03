<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\PaymentMethod;
use App\Models\CashClose;
use App\Models\Usuario;
use App\Models\Balance;
use App\Models\Headquarters;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        $methods = PaymentMethod::where('estado', 0)
            ->get();
        $headquarters = Headquarters::where('estado', 0)
            ->get();
        $users = Usuario::where('activo', 1)
            ->get();

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $headquarter_id = $request->headquarter_id;
        $payment_method_id = $request->payment_method_id;
        $user_id = $request->user_id;

        $query = Payment::with(['sale.headquarter', 'usuario', 'paymentMethod'])
            ->where('estado', 0)
            ->when($start_date, fn($query) => $query->where('fecha', '>=', $start_date))
            ->when($end_date, fn($query) => $query->where('fecha', '<=', $end_date))
            ->when($user_id, fn($query) => $query->where('user_id', $user_id))
            ->when($headquarter_id === 'sin_sede', function ($query) { //cuando es delivery es sin sede
                $query->whereHas('sale', function ($q) {
                    $q->whereIn('type_sale', [2, 3]);
                });
            })
            ->when($headquarter_id !== 'sin_sede' && !is_null($headquarter_id), function ($query) use ($headquarter_id) {
                $query->whereHas('sale', function ($q) use ($headquarter_id) {
                    $q->where('headquarter_id', $headquarter_id)->whereIn('type_sale', [0, 1]);
                });
            })
            ->when($payment_method_id, fn($query) => $query->where('payment_method_id', $payment_method_id))
            ->orderBy('fecha', 'desc');

        $total = $query->sum('monto');
        $payments = $query->paginate(20);

        return view('payments.index', compact('payments', 'methods', 'total', 'headquarters', 'users'));
    }


    public function listar(Request $request)
    {
        try {
            $sale_id = $request->sale_id;

            $payments = Payment::where('sale_id', '=',  $sale_id)
                ->orderBy('fecha')
                ->with('paymentMethod')
                ->get()
                ->map(function ($payment) {
                    return [
                        'monto' => $payment->monto,
                        'fecha' => Carbon::parse($payment->fecha)->format('Y-m-d'),
                        'metodo_pago' => $payment->paymentMethod->nombre,
                    ];
                });

            return response()->json([
                'status' => true,
                'payments' => $payments,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al listar pagos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            $monto = $request->monto;
            $metodo_pago_id = $request->metodo_pago;
            $fecha = $request->fecha;

            $payment = Payment::create([
                'sale_id' => $sale_id,
                'estado' => 0,
                'fecha' => $fecha,
                'monto' => $monto,
                'payment_method_id' => $metodo_pago_id,
            ]);


            $payments = Payment::where('sale_id', '=',  $sale_id)
                ->orderBy('fecha')
                ->with('paymentMethod')
                ->get()
                ->map(function ($payment) {
                    return [
                        'monto' => $payment->monto,
                        'fecha' => $payment->fecha,
                        'metodo_pago' => $payment->paymentMethod->nombre,
                    ];
                });


            return response()->json([
                'status' => true,
                'payments' => $payments,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cashClose(Request $request)
    {
        $date = $request->date ? $request->date : now()->toDateString();

        // ¿El usuario es delivery?
        $isDelivery = auth()->user()->hasRole('delivery');

        // Delivery puede consultar cualquier turno (default: mañana=0). Otros: su turno actual.
        if ($isDelivery) {
            $turno = $request->turno !== null ? (int) $request->turno : 0;
        } else {
            $turno = auth()->user()->turno;
        }

        // Sede: delivery no filtra por sede; otros sí
        $sede = $isDelivery ? null : auth()->user()->sede_id;

        // Monto de apertura de caja para la fecha/turno/sede
        $monto = CashClose::where('estado', 0)
            ->where('fecha', $date)
            ->where('turno', $turno)
            ->where('headquarter_id', $sede)
            ->value('monto');

        // Tipos de venta (según si es delivery o no)
        $venta_directa    = $isDelivery ? [2, 0] : [2, 0];
        $venta_anticipada = $isDelivery ? [3, 1] : [3, 1];

        $VENTA_DIRECTA = [0, 2];      // Directa normal (0) y Delivery directa (2)
        $VENTA_ANTICIPADA = [1, 3]; 
        $userId = $isDelivery ? auth()->id() : $request->input('user_id');

        // =========================
        // VENTAS DIRECTAS (totales por método)
        // =========================
        $ventas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_directa, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_directa, $isDelivery) {
                        $q->whereIn('type_sale', $venta_directa)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_ventas = $ventas_payment_methods->sum('total');

        // =========================
        // ANTICIPADAS (totales por método - todas)
        // =========================
        $anticipadas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->whereIn('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas = $anticipadas_payment_methods->sum('total');

        // =====================================================
        // ANTICIPADAS DIVIDIDAS: PAGO INICIAL vs PAGOS PENDIENTES
        // =====================================================

        // 1) Pago inicial: es el primer pago (más antiguo) de la venta
        $anticipadas_inicial_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->whereIn('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    // Este pago debe ser el PRIMERO de su venta
                    ->whereRaw("
                    payments.id = (
                        SELECT p2.id
                        FROM payments AS p2
                        WHERE p2.sale_id = payments.sale_id
                          AND p2.estado = 0
                        ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                        LIMIT 1
                    )
                ")
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas_iniciales = $anticipadas_inicial_payment_methods->sum('total');

        // 2) Pagos pendientes: cualquier pago de la venta que NO sea el primero
        $anticipadas_pendiente_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->whereIn('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    // Este pago NO es el primero de su venta
                    ->whereRaw("
                    payments.id <> (
                        SELECT p2.id
                        FROM payments AS p2
                        WHERE p2.sale_id = payments.sale_id
                          AND p2.estado = 0
                        ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                        LIMIT 1
                    )
                ")
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas_pendientes = $anticipadas_pendiente_payment_methods->sum('total');

        // =====================================================
        // EFECTIVO (directas + anticipadas) - solo método efectivo (id=2)
        // =====================================================
        $ventaDirecta = Payment::where('estado', 0)
            ->where('payment_method_id', 2)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->where('user_id', auth()->user()->id)
            ->whereHas('sale', function ($q) use ($VENTA_DIRECTA, $sede) {
                $q->whereIn('type_sale', $VENTA_DIRECTA)  // Solo ventas directas
                    ->where('estado', 0)
                    ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
            })
            ->sum('monto');

        // Suma para ventas anticipadas
        $ventaAnticipada = Payment::where('estado', 0)
            ->where('payment_method_id', 2)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->where('user_id', auth()->user()->id)
            ->whereHas('sale', function ($q) use ($VENTA_ANTICIPADA, $sede) {
                $q->whereIn('type_sale', $VENTA_ANTICIPADA)  // Solo ventas anticipadas
                    ->where('estado', 0)
                    ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
            })
            ->sum('monto');

        // Suma total
        $efectivo = $ventaDirecta + $ventaAnticipada;
    
        // =====================================================
        // GASTOS
        // =====================================================
        $gastos = Expense::with('details')
            ->where('estado', 0)
            ->whereDate('date', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($sede, fn($q) => $q->where('sede_id', $sede))
            ->get()
            ->flatMap->details
            ->sum('subtotal');
            
        // Delivery no descuenta gastos
        if ($isDelivery) {
            $gastos = 0;
        }

        $saldo = $efectivo - $gastos;

        return view('cashClose.index', compact(
            'ventaDirecta',
            'ventaAnticipada',
            'efectivo',
            'gastos',
            'saldo',
            'ventas_payment_methods',
            'anticipadas_payment_methods',
            'total_ventas',
            'total_anticipadas',
            'date',
            'monto',
            'turno',
            'isDelivery',
            // Nuevos arreglos/totales de anticipadas divididas
            'anticipadas_inicial_payment_methods',
            'anticipadas_pendiente_payment_methods',
            'total_anticipadas_iniciales',
            'total_anticipadas_pendientes'
        ));
    }

    public function cashCloseHistory(Request $request)
    {
        $date   = $request->input('date');
        $turno  = $request->input('turno');
        $sede   = $request->input('headquarter_id');
        $userId = $request->input('user_id');


        // Filtros del formulario
        if (is_null($date)) {
            $date = now()->toDateString();
            $request->merge(['date' => $date]);
        }
        if (is_null($turno)) {
            $turno = auth()->user()->turno;
            $request->merge(['turno' => $turno]);
        }
        if (is_null($sede)) {
            $sede = auth()->user()->sede_id;
            $request->merge(['headquarter_id' => $sede]);
        }
        if (is_null($userId)) {
            $userId = auth()->user()->id;
            $request->merge(['user_id' => $userId]);
        } 

        $arqueos = Balance::where('headquarter_id',$sede)
            ->where('turno',$turno)
            ->where('usuario_id',$userId)
            ->where('fecha',$date)
            ->get();

    
        // Catálogos para selects
        $sedes = Headquarters::where('estado', 0)->orderBy('nombre')->get();
    
        $usuarios = Usuario::orderBy('nombre')
            ->when($sede, function ($q) use ($sede) {
                $q->where(function ($qq) use ($sede) {
                    $qq->where('sede_id', $sede)
                        ->orWhereNull('sede_id');
                })
                    ->where('rol_id', '!=', 5);
            }, function ($q) {
                $q->where('rol_id', 5);
            })
            ->get();
    
        $todosLosUsuarios = Usuario::orderBy('nombre')->get();
    
        // IDs de tipo de venta - USAR TODOS LOS TIPOS
        $VENTA_DIRECTA = [0, 2];      // Directa normal (0) y Delivery directa (2)
        $VENTA_ANTICIPADA = [1, 3];   // Anticipada normal (1) y Delivery anticipada (3)
    
        // ===========================
        // VENTAS DIRECTAS (totales por método)
        // ===========================
        $ventas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_DIRECTA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_DIRECTA, $sede) {
                        $q->whereIn('type_sale', $VENTA_DIRECTA)  // Usar array de tipos
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    ->sum('monto');
                $method->total = $total;
                return $method;
            });
    
        $total_ventas = $ventas_payment_methods->sum('total');
    
        // ===========================
        // ANTICIPADAS (totales por método - todas)
        // ===========================
        $anticipadas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_ANTICIPADA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_ANTICIPADA, $sede) {
                        $q->whereIn('type_sale', $VENTA_ANTICIPADA)  // Usar array de tipos
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    ->sum('monto');
                $method->total = $total;
                return $method;
            });
    
        $total_anticipadas = $anticipadas_payment_methods->sum('total');
    
        // =====================================================
        // ANTICIPADAS DIVIDIDAS: PAGO INICIAL vs PAGOS PENDIENTES
        // =====================================================
    
        // 1) Pago inicial: es el primer pago (más antiguo) de la venta
        $anticipadas_inicial_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_ANTICIPADA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_ANTICIPADA, $sede) {
                        $q->whereIn('type_sale', $VENTA_ANTICIPADA)  // Usar array de tipos
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    // Este pago debe ser el PRIMERO de su venta
                    ->whereRaw("
                        payments.id = (
                            SELECT p2.id
                            FROM payments AS p2
                            WHERE p2.sale_id = payments.sale_id
                              AND p2.estado = 0
                            ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                            LIMIT 1
                        )
                    ")
                    ->sum('monto');
    
                $method->total = $total;
                return $method;
            });
    
        $total_anticipadas_iniciales = $anticipadas_inicial_payment_methods->sum('total');
    
        // 2) Pagos pendientes: cualquier pago de la venta que NO sea el primero
        $anticipadas_pendiente_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_ANTICIPADA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_ANTICIPADA, $sede) {
                        $q->whereIn('type_sale', $VENTA_ANTICIPADA)  // Usar array de tipos
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    // Este pago NO es el primero de su venta
                    ->whereRaw("
                        payments.id <> (
                            SELECT p2.id
                            FROM payments AS p2
                            WHERE p2.sale_id = payments.sale_id
                              AND p2.estado = 0
                            ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                            LIMIT 1
                        )
                    ")
                    ->sum('monto');
    
                $method->total = $total;
                return $method;
            });
    
        $total_anticipadas_pendientes = $anticipadas_pendiente_payment_methods->sum('total');
    
        // =====================================================
        // EFECTIVO (directas + anticipadas) - solo método efectivo (id=2)
        // =====================================================
        $efectivo = Payment::where('estado', 0)
            ->where('payment_method_id', 2)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereHas('sale', function ($q) use ($VENTA_DIRECTA, $VENTA_ANTICIPADA, $sede) {
                $q->whereIn('type_sale', array_merge($VENTA_DIRECTA, $VENTA_ANTICIPADA))  // Todos los tipos
                    ->where('estado', 0)
                    ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
            })
            ->sum('monto');
    
        // =====================================================
        // GASTOS
        // =====================================================
        $gastos = Expense::with('details')
            ->where('estado', 0)
            ->whereDate('date', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($sede, fn($q) => $q->where('sede_id', $sede))
            ->get()
            ->flatMap->details
            ->sum('subtotal');
    
        $saldo = $efectivo - $gastos;
    
        // MONTO DE APERTURA - corregido
        $monto = CashClose::where('estado', 0)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($sede, fn($q) => $q->where('headquarter_id', $sede))
            ->when($userId, fn($q) => $q->where('usuario_id', $userId)) // Agregar filtro por usuario
            ->value('monto');
    
        // Variable adicional para coincidencia con cashClose
        $isDelivery = false; // En el histórico no se aplica lógica delivery
    
        return view('cashClose.historico', compact(
            // 'date',
            // 'turno',
            // 'sede',
            // 'userId',
            'arqueos',
            'sedes',
            'usuarios',
            'ventas_payment_methods',
            'anticipadas_payment_methods',
            'total_ventas',
            'total_anticipadas',
            'efectivo',
            'gastos',
            'saldo',
            'monto',
            'todosLosUsuarios',
            'isDelivery',
            // NUEVAS VARIABLES - igual que cashClose
            'anticipadas_inicial_payment_methods',
            'anticipadas_pendiente_payment_methods',
            'total_anticipadas_iniciales',
            'total_anticipadas_pendientes'
        ));
    }


    public function storeCashClose(Request $request)
    {
        try {
            $isDelivery = auth()->user()->hasRole('delivery');

            // Delivery no puede guardar cierres de caja
            if ($isDelivery) {
                return response()->json([
                    'status' => false,
                    'error' => 'Los usuarios delivery no pueden guardar cierres de caja.'
                ], 403);
            }

            $fecha = $request->fecha;
            $monto = $request->monto;
            $turno = auth()->user()->turno; // Usuario normal siempre usa su turno
            $usuario_id = auth()->user()->id;
            $headquarter_id = auth()->user()->sede_id;

            $cierre = CashClose::updateOrCreate(
                [
                    'fecha' => $fecha,
                    'turno' => $turno,
                    'headquarter_id' => $headquarter_id,
                ],
                [
                    'usuario_id' => $usuario_id,
                    'monto' => $monto,
                    'estado' => 0,
                ]
            );

            return response()->json([
                'status' => true,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar cierre: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cashClosePDF(Request $request)
    {
        try {
            Log::info('cashClosePDF recibe:', $request->all());
            $request->validate([
                'user_id' => 'nullable|exists:usuarios,id', // Cambiar a 'usuarios'
                'turno' => 'nullable|numeric|in:0,1',
                'headquarter_id' => 'nullable|exists:headquarters,id',
                'tabla' => 'required',
                'date' => 'required|date',
                'monto' => 'nullable|numeric',
                'efectivo' => 'nullable|numeric' // Cambiar de 'total' a 'efectivo'
            ]);
    
            $user_id = $request->user_id ?? auth()->user()->id;
            $user = Usuario::find($user_id)->nombre;
            $turn = $request->turno ?? auth()->user()->turno;
            if ($turn === 0) {
                $turno = 'mañana';
            } else {
                $turno = 'tarde';
            }
            $headquarter_id = $request->headquarter_id ?? auth()->user()->sede_id;
            $headquarter = Headquarters::find($headquarter_id)->nombre;
            $tabla = $request->tabla;
            $fecha = $request->date;
            $monto = $request->monto ?? 0;
            $efectivo = $request->efectivo ?? 0; // Cambiar variable
            
            // Calcular la diferencia (monto real - efectivo del sistema)
            $diferencia = $monto - $efectivo;
    
            $pdf = Pdf::loadView('cashClose.pdf', compact(
                'user', 
                'turno', 
                'headquarter', 
                'tabla', 
                'fecha', 
                'monto',
                'efectivo',  // Cambiar de 'total' a 'efectivo'
                'diferencia'
            ));
    
            // Descargar el archivo PDF
            return $pdf->download('Cierre.pdf');
        } catch (\Throwable $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response('Error generando PDF: ' . $e->getMessage(), 500);
        }
    }

    public function pdf(Request $request)
    {
        try {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $payment_method_id = $request->payment_method_id;
            $payment_name = $payment_method_id ? PaymentMethod::find($payment_method_id)->nombre : null;

            $payments = Payment::with('usuario', 'paymentMethod')
                ->where('estado', 0)
                ->when($start_date, fn($query) => $query->where('fecha', '>=', $start_date))
                ->when($end_date, fn($query) => $query->where('fecha', '<=', $end_date))
                ->when($payment_method_id, fn($query) => $query->where('payment_method_id', $payment_method_id))
                ->orderBy('fecha', 'desc')
                ->get();

            $data = [
                'payments' => $payments,
                'filters' => [
                    'Desde' => $start_date,
                    'Hasta' => $end_date,
                    'Método de pago' => $payment_name
                ]
            ];

            $pdf = Pdf::loadView('payments.pdf', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_pagos_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar pdf: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pdfAgrupado(Request $request)
    {
        try {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $headquarter_id = $request->headquarter_id;
            $headquarter_name = null;
            if ($headquarter_id) {
                if ($headquarter_id == "sin_sede") {
                    $headquarter_name = "Sin sede";
                } else {
                    $headquarter_name = Headquarters::find($headquarter_id)->nombre;
                }
            }

            $payment_method_id = $request->payment_method_id;
            $payment_name = $payment_method_id ? PaymentMethod::find($payment_method_id)->nombre : null;

            $payments = Payment::with('usuario', 'paymentMethod')
                ->where('payments.estado', 0)
                ->when($start_date, fn($query) => $query->where('fecha', '>=', $start_date))
                ->when($end_date, fn($query) => $query->where('fecha', '<=', $end_date))
                ->when($headquarter_id === 'sin_sede', function ($query) { //cuando es delivery es sin sede
                    $query->whereHas('sale', function ($q) {
                        $q->whereIn('type_sale', [2, 3]);
                    });
                })
                ->when($headquarter_id !== 'sin_sede' && !is_null($headquarter_id), function ($query) use ($headquarter_id) {
                    $query->whereHas('sale', function ($q) use ($headquarter_id) {
                        $q->where('headquarter_id', $headquarter_id)->whereIn('type_sale', [0, 1]);
                    });
                })
                ->when($payment_method_id, fn($query) => $query->where('payment_method_id', $payment_method_id))
                ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
                ->where('payment_methods.estado', 0)
                ->select('payment_methods.nombre', \DB::raw('SUM(payments.monto) as total'))
                ->groupBy('payment_methods.nombre')
                ->orderBy('payment_methods.nombre', 'asc')
                ->get();

            $data = [
                'payments' => $payments,
                'filters' => [
                    'Desde' => $start_date,
                    'Hasta' => $end_date,
                    'Método de pago' => $payment_name,
                    'Sede' => $headquarter_name
                ]
            ];

            $pdf = Pdf::loadView('payments.pdfAgrupado', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_pagos_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar pdf: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMethod(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|exists:payments,id',
                'payment_method_id' => 'required|exists:payment_methods,id'
            ]);

            $payment = Payment::findOrFail($request->payment_id);
            $payment->update([
                'payment_method_id' => $request->payment_method_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Método de pago actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el método de pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function arqueoStore(Request $request)
    {
        try {
            $request->validate([
                'fecha' => 'required|date',
                'turno' => 'required|numeric|in:0,1',
                'sede' => 'required|exists:headquarters,id',
                'usuario' => 'required|exists:usuarios,id',
                'balances' => 'required|array',
            ]);

            $fecha = $request->fecha;
            $turno = $request->turno;
            $headquarter_id = $request->sede;
            $usuario_id = $request->usuario;
            $balances = $request->balances;

            // Guardar cada balance en la tabla create
            foreach ($balances as $balance) {
                if(!is_null($balance['value'])){
                    Balance::updateOrCreate(
                        [
                            'payment_method_id' => $balance['payment_method_id'],
                            'headquarter_id'    => $headquarter_id,
                            'turno'             => $turno,
                            'usuario_id'        => $usuario_id,
                            'fecha'             => $fecha,
                        ],
                        [
                            'monto' => $balance['value']
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Arqueo guardado correctamente'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }
}
