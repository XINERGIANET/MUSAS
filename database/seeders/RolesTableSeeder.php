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
        Rol::updateOrCreate(
            ['nombre' => 'Admin'],
            ['descripcion' => 'Administrador del sistema']
        );

        Rol::updateOrCreate(
            ['nombre' => 'Xinergia'],
            ['descripcion' => 'Administrador maestro del sistema']
        );
    }
}
