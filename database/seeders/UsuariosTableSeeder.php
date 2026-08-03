<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        Usuario::create([
            'nombre' => 'xinergia',
            'email' => 'xinergia',
            'password' => Hash::make('musas'),
            'rol_id' => 2, // ID del rol Admin
            'activo' => true,
        ]);
    }
}
