<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        // Filtrar solo los registros con estado = 0 (activos)
        $clients = Client::where('estado', 0)->paginate(5);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateClient($request);

        // Crear el registro con estado = 0 (activo)
        Client::create(array_merge($validatedData, ['estado' => 0]));

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $client = Client::findOrFail($id);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validatedData = $this->validateClient($request);

        // Obtener el registro por ID y actualizarlo
        $client = Client::findOrFail($id);
        $client->update($validatedData);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 1 (eliminado)
        $client = Client::findOrFail($id);
        $client->update(['estado' => 1]);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    protected function validateClient(Request $request)
    {
        // Validar los campos del formulario
        return $request->validate([
            'ruc_dni' => 'required|string|max:20',
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
        ]);
    }
}
