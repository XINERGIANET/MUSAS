<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        // Filtrar solo los registros con estado = 0 (activos)
        $suppliers = Supplier::where('estado', 0)->paginate(30);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateSupplier($request);

        // Crear el registro con estado = 0 (activo)
        Supplier::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateSupplier($request);

        // Obtener el registro por ID y actualizarlo
        $supplier = Supplier::findOrFail($id);
        $supplier->update($validatedData);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $supplier = Supplier::findOrFail($id);
        $supplier->update(['estado' => 1]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }

    protected function validateSupplier(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'ruc' => 'required|string|max:20',
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'dias_pago' => 'nullable|integer|min:0',
        ]);
    }

    public function buscar(Request $request)
    {
        $query = $request->input('query'); // Obtener el término de búsqueda

        // Buscar proveedores por razon_social
        $proveedores = Supplier::where('razon_social', 'LIKE', "%{$query}%")
                                ->select('id', 'razon_social') // Seleccionar solo id y razon_social
                                ->limit(10) // Limitar resultados
                                ->get();

        return response()->json($proveedores); // Devolver resultados en JSON
    }

    public function filtro(Request $request)
    {
        $query = $request->get('query');

        $supplier = Supplier::where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre_comercial', 'like', "%$query%")
                             ->orWhere('razon_social', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['supplier' => $supplier]);
    }

    public function api(Request $request){
        $providers = Supplier::where('estado', 0)->where('razon_social', 'like', '%'.$request->q.'%')->get();

        return response()->json([
            'results' => $providers->map(function($provider){
                return [
                    'id' => $provider->id,
                    'text' => $provider->razon_social,
                ];
            })
        ]);
    }

    public function getAllSuppliers()
    {
        $suppliers = Supplier::where('estado', 0)->get();
        return response()->json([
            'results' => $suppliers->map(function($provider){
                return [
                    'id' => $provider->id,
                    'razon_social' => $provider->razon_social, // Cambiar 'text' por 'razon_social'
                ];
            })
        ]);
    }
}
