<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use Illuminate\Http\Request;

class PuestoController extends Controller
{
        // Mostrar todas las sedes
        public function index()
        {
            $puestos = Puesto::where('estado', 0)->paginate(5);
            return view('puestos.index', compact('puestos'));            
        }
    
        // Mostrar el formulario para crear una nueva sede
        public function create()
        {
            return view('puestos.create');
        }
    
        // Guardar una nueva sede
        public function store(Request $request)
        {
            $validatedData = $this->validatePuesto($request);
    
            Puesto::create(array_merge($validatedData, ['estado' => 0]));
    
            return redirect()->route('puestos.index')
                ->with('success', 'Puesto creada exitosamente.');
        }
    
        public function show($id)
        {
            $puesto = Puesto::findOrFail($id);
            return response()->json($puesto);
        }
    
        public function edit($id)
        {
            $puesto = Puesto::findOrFail($id);
            return view('puestos.edit', compact('puesto'));
        }
    
    
        // Actualizar una sede
        public function update(Request $request, $id)
        {
            $validatedData = $this->validatePuesto($request);
    
            $puesto = Puesto::findOrFail($id);
            $puesto->update($validatedData);
    
            return redirect()->route('puestos.index')
                ->with('success', 'Materia Prima actualizado correctamente.');
        }
    
        // Eliminar un puestoo
        public function destroy($id)
        {
            $puesto = Puesto::findOrFail($id);
            $puesto->update(['estado' => 1]); // Cambiar estado a 1 (eliminado)
            return redirect()->route('puestos.index')
                ->with('success', 'puestoo eliminado correctamente.');
        }
    
    
        protected function validatePuesto(Request $request)
        {
            return $request->validate([
                'nombre' => 'required|string|max:255',
            ]);
        }
}
