<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use Illuminate\Http\Request;

class HeadquartersController extends Controller
{
    // Mostrar todas las sedes
    public function index()
    {
        $headquarters = Headquarters::where('estado', 0)->paginate(5);
        return view('headquarters.index', compact('headquarters'));
    }

    // Mostrar el formulario para crear una nueva sede
    public function create()
    {
        return view('headquarters.create');
    }

    // Guardar una nueva sede
    public function store(Request $request)
    {
        $validatedData = $this->validateHeadquarters($request);

        // Crear el registro con estado = 0 (activo)
        Headquarters::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('headquarters.index')
            ->with('success', 'Sede creada exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $headquarters = Headquarters::findOrFail($id);
        return response()->json($headquarters);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $headquarters = Headquarters::findOrFail($id);
        return view('headquarters.edit', compact('headquarters'));
    }


    // Actualizar una sede
    public function update(Request $request, $id)
    {
        $validatedData = $this->validateHeadquarters($request);

        // Obtener el registro por ID y actualizarlo
        $headquarters = Headquarters::findOrFail($id);
        $headquarters->update($validatedData);

        return redirect()->route('headquarters.index')
            ->with('success', 'Sede actualizada exitosamente.');
    }

    // Eliminar una sede
    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $headquarters = Headquarters::findOrFail($id);
        $headquarters->update(['estado' => 1]);

        return redirect()->route('headquarters.index')
            ->with('success', 'Método de pago eliminado exitosamente.');
    }

    protected function validateHeadquarters(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
    }
}
