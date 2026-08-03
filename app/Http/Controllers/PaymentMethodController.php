<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        // Filtrar solo los registros con estado = 0 (activos)
        $paymentMethods = PaymentMethod::where('estado', 0)->paginate(5);
        return view('payment_methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('payment_methods.create');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validatedData = $this->validatePaymentMethod($request);

        $nombreLimpio = $request->input('nombre');
        $existe = PaymentMethod::where('nombre', $nombreLimpio)->where('estado', 0)->exists();
        if ($existe) {
            return redirect()->route('payment_methods.create')
                ->with('status', false)
                ->with('message', 'Ya existe un Método con ese nombre');
        }

        PaymentMethod::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('payment_methods.index')
            ->with('success', 'Método de pago creado exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $paymentMethod = PaymentMethod::findOrFail($id);
        return response()->json($paymentMethod);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $paymentMethod = PaymentMethod::findOrFail($id);
        return view('payment_methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validatedData = $this->validatePaymentMethod($request);

        $nombreLimpio = $request->input('nombre');
        $existe = PaymentMethod::where('nombre', $nombreLimpio)
        ->where('estado', 0)
        ->where('id', '!=', $id)
        ->exists();

        if ($existe) {
            return redirect()->route('payment_methods.index')
                ->with('status', false)
                ->with('message', 'Ya existe un Método con ese nombre');
        }

        // Obtener el registro por ID y actualizarlo
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update($validatedData);

        return redirect()->route('payment_methods.index')
            ->with('success', 'Método de pago actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update(['estado' => 1]);

        return redirect()->route('payment_methods.index')
            ->with('success', 'Método de pago eliminado exitosamente.');
    }

    protected function validatePaymentMethod(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
    }
}
