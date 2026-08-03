<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuariosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Rol::firstOrCreate(
            ['nombre' => 'Xinergia'],
            ['descripcion' => 'Administrador maestro del sistema']
        );

        Usuario::updateOrCreate([
            'email' => 'xinergia',
        ], [
            'nombre' => 'xinergia',
            'password' => Hash::make('xinergia'),
            'rol_id' => $role->id,
            'activo' => true,
            'turno' => 0,
        ]);
    }
}
