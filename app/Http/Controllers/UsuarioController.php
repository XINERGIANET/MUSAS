<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use Illuminate\Support\Facades\Log; // Importa la fachada Log
use Illuminate\Support\Facades\DB; // Importa la fachada DB
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\UseUse;


class UsuarioController extends Controller
{
    public function index()
    {
        // Filtrar solo los registros con estado = 1 (activos)
        $roles = Rol::all(); // Obtener todos los roles para el formulario de creación
        $usuarios = Usuario::with(['rol', 'headquarter'])->where('activo', 1)->paginate(30);
        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        /// Validar los datos del formulario
        $validatedData = $this->validateUsuario($request);

        // Crear el registro
        Usuario::create([
            'nombre' => $validatedData['nombre'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Encriptar la contraseña
            'rol_id' => $validatedData['rol_id'],
            'pin' => $validatedData['pin'] ?? null, // Incluir PIN si se proporciona
            'turno' => 0,
            'activo' => 1, // Por defecto, el usuario está activo
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $usuario = Usuario::findOrFail($id);
        return response()->json($usuario);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all(); // Obtener todos los roles
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, $id)
    {
        // Debug: Log de los datos recibidos
        Log::info('Datos recibidos para actualizar usuario:', $request->all());
        
        // Validar los datos del formulario
        $validatedData = $this->validateUsuario($request, $id);
        
        // Debug: Log de los datos validados
        Log::info('Datos validados:', $validatedData);

        // Obtener el registro por ID y preparar datos para actualizar
        $usuario = Usuario::findOrFail($id);
        $updateData = [
            'nombre' => $validatedData['nombre'],
            'email' => $validatedData['email'],
            'rol_id' => $validatedData['rol_id'],
            'activo' => $request->has('activo') ? 1 : 0, // Actualizar el estado
        ];

        // Si se proporciona una nueva contraseña, incluirla
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validatedData['password']);
        }

        // Si se proporciona un PIN, incluirlo
        if ($request->has('pin')) {
            $updateData['pin'] = $validatedData['pin'];
        }

        // Debug: Log de los datos que se van a actualizar
        Log::info('Datos para actualizar:', $updateData);

        // Actualizar el usuario con todos los datos
        $usuario->update($updateData);
        
        // Debug: Log del usuario después de la actualización
        Log::info('Usuario después de actualización:', $usuario->fresh()->toArray());

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        // Obtener el registro por ID y cambiar su estado a 0 (inactivo)
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['activo' => 0]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    protected function validateUsuario(Request $request, $id = null)
    {
        // Validar los campos del formulario
        $rules = [
            'nombre' => 'required|string|max:255',
            'email' => 'required|string',
            'rol_id' => 'required|exists:roles,id',
            'pin' => 'nullable|numeric|digits:4', // PIN opcional de 4 dígitos
        ];

        // Si es una creación (no hay ID), la contraseña es obligatoria
        // Si es una actualización (hay ID), la contraseña es opcional
        if ($id) {
            $rules['password'] = 'nullable|string|min:6';
        } else {
            $rules['password'] = 'required|string|min:6';
        }

        return $request->validate($rules);
    }

    public function filtro(Request $request)
    {
        $query = $request->get('query');

        $user = Usuario::with(['rol', 'headquarter'])
            ->where('activo', 1)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subquery) use ($query) {
                    $subquery->where('nombre', 'like', "%$query%");
                });
            })
            ->get();

        return response()->json(['user' => $user]);
    }

    public function setTurno(Request $request)
    {
        $request->validate([
            'turno' => 'required|string|max:50',
        ]);
    
        $authUser = Auth::user();
        $user = Usuario::find($authUser->id);
        $user->turno = $request->input('turno');
        $user->save();
    
        // Elimina la variable de sesión para que el modal no vuelva a aparecer
        $request->session()->forget('show_turno_modal');
    
        return response()->json(['success' => true]);
    }

    public function cambiarTurno(Request $request)
    {   
        try{

            $turno = $request->turno;
            auth()->user()->update([
                'turno' => $turno
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Turno actualizado',
            ]);

        }catch(\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al actualizar el turno: ' . $e->getMessage(),
            ], 500);
        }
       
    }

    public function cambiarSede(Request $request)
    {   
        try{

            $sede_id = $request->sede;
            auth()->user()->update([
                'sede_id' => $sede_id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Sede actualizada',
            ]);

        }catch(\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al actualizar la sede: ' . $e->getMessage(),
            ], 500);
        }
       
    }
}