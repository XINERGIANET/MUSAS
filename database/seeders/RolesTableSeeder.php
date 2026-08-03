<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rol::create(['nombre' => 'Admin', 'descripcion' => 'Administrador del sistema']);
        Rol::create(['nombre' => 'Xinergia', 'descripcion' => 'Maestro regular']);
    }
}
