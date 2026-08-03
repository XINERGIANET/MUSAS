<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios'; // Especifica el nombre de la tabla

    protected $fillable = [
        'id',
        'nombre',
        'email',
        'password',
        'pin',
        'rol_id',
        'sede_id',
        'turno',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('activo', 1);
    }

    // Relación con el modelo Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function hasRole($rol)
    {
        return $this->rol && $rol == $this->rol->nombre;
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class, 'sede_id');
    }

    public function username()
    {
        return 'email'; // Cambia esto si usas otro campo
    }
 
    public function isSedeRestaurante()
    {
        return $this->sede_id == 1; // si es Balta
    }

    public function isXinergia()
    {
        return $this->email == 'xinergia';
    }
}
