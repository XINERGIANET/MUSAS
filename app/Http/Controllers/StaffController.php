<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use App\Models\Puesto;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        // Filtrar solo los registros con estado = 0 (activos)
        $headquarters = Headquarters::where('estado', 0)->get();
        $staff = Staff::where('estado', 0)->paginate(30);
        $puestos = Puesto::where('estado', 0)->get();
        return view('staff.index', compact('staff', 'headquarters', 'puestos'));
    }

    public function create()
    {
        $headquarters = Headquarters::where('estado', 0)->get();
        $puestos = Puesto::where('estado', 0)->get();
        return view('staff.create', compact('headquarters', 'puestos'));
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateStaff($request);

        // Crear el registro con estado = 0 (activo)
        Staff::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('staff.index')
            ->with('success', 'Staff creado exitosamente.');
    }

    public function storeAjax(Request $request)
    {
        try {
            $validatedData = $this->validateStaff($request);
            $staff = Staff::create(array_merge($validatedData, ['estado' => 0]));

            return response()->json([
                'success' => true,
                'message' => 'Encargado agregado exitosamente',
                'data' => [
                    'id' => $staff->id,
                    'nombre' => $staff->nombre,
                    'puesto_id' => $staff->puesto_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el encargado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $staff = Staff::findOrFail($id);
        return response()->json($staff);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $staff = Staff::findOrFail($id);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateStaff($request);

        // Obtener el registro por ID y actualizarlo
        $staff = Staff::findOrFail($id);
        $staff->update($validatedData);

        return redirect()->route('staff.index')
            ->with('success', 'Staff actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $staff = Staff::findOrFail($id);
        $staff->update(['estado' => 1]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff eliminado exitosamente.');
    }

    protected function validateStaff(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'dni' => 'nullable|string|max:20',
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'puesto_id' => 'nullable|exists:puestos,id',
            'headquarter_id' => 'nullable|exists:headquarters,id',
            'fecha_nacimiento' => 'nullable|date', // Validar como fecha
            'sueldo' => 'nullable|numeric|min:0' // Validar como número y mayor o igual a 0
        ]);
    }

    public function filtro(Request $request)
    {
        $query = $request->get('query');

        $staff = Staff::with('headquarter')
            ->where('estado', 0)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['staff' => $staff]);
    }
}
