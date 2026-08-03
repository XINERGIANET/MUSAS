<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\PaymentMethod;
use App\Models\Headquarters;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $query = Expense::with('paymentMethod', 'supplier', 'sede')
            ->when($start_date, function ($q) use ($start_date) {
                $q->whereDate('fecha', '>=', $start_date);
            })
            ->when($end_date, function ($q) use ($end_date) {
                $q->whereDate('fecha', '<=', $end_date);
            });

        if ($user->sede_id && !auth()->user()->hasRole('admin')) {
            $query->where('sede_id', $user->sede_id);
        }

        $expenses = $query->paginate(5);
        $allExpenses = (clone $query)->get(); // para calcular el total sin alterar paginación
        $total = $allExpenses->sum('monto');

        $metodosPago = PaymentMethod::where('estado', 0)->get();
        $sede = Headquarters::where('estado', 0)->get();

        return view('expenses.index', compact('expenses', 'total', 'metodosPago', 'sede'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();
        $metodosPago = PaymentMethod::where('estado', 0)->get();

        // Solo los admin pueden ver la lista de sedes
        $sede = Headquarters::where('estado', 0)->get();

        return view('expenses.create', compact('metodosPago', 'sede'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_comprobante' => 'required|string|max:250',
            'invoice_number' => 'nullable|string|max:250',
            'detalle' => 'required|string',
            'monto' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'sede_id' => 'nullable|exists:headquarters,id',
        ]);

        $request->merge(['turno' => auth()->user()->turno]);

        Expense::create($request->all());

        return redirect()->route('expenses.index')->with('success', 'Egreso registrado correctamente.');
    }


    public function expensecash(Request $request)
    {

        return view('expenses.cash');
    }

    public function historyexpensecash(Request $request)
    {
        $user = auth()->user();
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');

        $expenses = Expense::with('details')
            ->where('user_id', $user->id)
            ->when($fecha_inicio, function($q) use ($fecha_inicio) {
                $q->whereDate('created_at', '>=', $fecha_inicio);
            })
            ->when($fecha_fin, function($q) use ($fecha_fin) {
                $q->whereDate('created_at', '<=', $fecha_fin);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('expenses.historycash', compact('expenses'));
    }

    public function storeExpenseCash(Request $request)
    {
        $user = auth()->user();
        $turno = $user->turno; // Ajusta según tu modelo
        $sede = $user->sede_id; // Ajusta según tu modelo

        $registros = $request->input('expensecash'); // Array de registros

        try {
            foreach ($registros as $registro) {
                // Crear el Expense
                $expense = new Expense();
                $expense->invoice_number = $registro['venta'] ?? null; // Número de venta o nulo
                $expense->user_id = $user->id;
                $expense->sede_id = $sede;
                $expense->description = $registro['descripcion'] ?? null;
                $expense->turno = $turno;
                $expense->payment_method_id = 2;
                $expense->save();

                // Crear el ExpenseDetail
                $detail = new ExpenseDetail();
                $detail->expense_id = $expense->id;
                $detail->product_id = $registro['producto_id'] ?? null; // Nuevo: id del producto
                $detail->unit_price = $registro['precio_unitario'] ?? null;
                $detail->quantity = $registro['cantidad'] ?? 1;
                $detail->subtotal = $registro['subtotal'] ?? 0;
                $detail->save();
            }
            return response()->json(['status' => true, 'message' => 'Egresos registrados correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_comprobante' => 'required|string|max:250',
            'invoice_number' => 'required|string|max:250',
            'detalle' => 'required|string',
            'monto' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'sede_id' => 'required|exists:headquarters,id',
        ]);

        if (auth()->user()->sede_id) {
            $request->merge(['sede_id' => auth()->user()->sede_id]);
        }

        $expense = Expense::findOrFail($id);
        $expense->update($request->all());

        return redirect()->route('expenses.index')->with('success', 'Egreso actualizado correctamente.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {}
}
