<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    /// Mostrar todas las sedes
    public function index(Request $request)
    {
        $search = request('search');
        
        $unidadMedidas = UnidadMedida::where('estado', 0)
        ->when($search, function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%');
            })
        ->paginate(30);

        return view('unidad_medidas.index', compact('unidadMedidas'));
    }

    // Mostrar el formulario para crear una nueva sede
    public function create()
    {
        return view('unidad_medidas.create');
    }

    // Guardar una nueva sede
    public function store(Request $request)
    {
        $validatedData = $this->validateUnidadMedida($request);

        $nombreLimpio = $request->input('nombre');
        $existe = UnidadMedida::where('nombre', $nombreLimpio)->where('estado', 0)->exists();
        if ($existe) {
            return redirect()->route('unidad_medidas.create')
                ->with('status', false)
                ->with('message', 'Ya existe una Unidad de Medida con ese nombre');
        }

        UnidadMedida::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('unidad_medidas.index')
            ->with('success', 'Sede creada exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $UnidadMedida = UnidadMedida::findOrFail($id);
        return response()->json($UnidadMedida);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $unidadMedidas = UnidadMedida::findOrFail($id);
        return view('unidad_medidas.edit', compact('unidadMedidas'));
    }


    // Actualizar una sede
    public function update(Request $request, $id)
    {
        $validatedData = $this->validateUnidadMedida($request);

        $nombreLimpio = $request->input('nombre');
        $existe = UnidadMedida::where('nombre', $nombreLimpio)
        ->where('estado', 0)
        ->where('id', '!=', $id)
        ->exists();

        if ($existe) {
            return redirect()->route('unidad_medidas.index')
                ->with('status', false)
                ->with('message', 'Ya existe una Unidad de Medida con ese nombre');
        }

        // Obtener el registro por ID y actualizarlo
        $unidadMedidas = UnidadMedida::findOrFail($id);
        $unidadMedidas->update($validatedData);

        return redirect()->route('unidad_medidas.index')
            ->with('success', 'Sede actualizada exitosamente.');
    }

    // Eliminar una sede
    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $unidadMedidas = UnidadMedida::findOrFail($id);
        $unidadMedidas->update(['estado' => 1]);

        return redirect()->route('unidad_medidas.index')
            ->with('success', 'Método de pago eliminado exitosamente.');
    }

    protected function validateUnidadMedida(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
    }


}