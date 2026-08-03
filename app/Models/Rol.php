<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles'; // Especifica el nombre de la tabla

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con el modelo Usuario
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}