<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'dni',
        'nombre',
        'telefono',
        'puesto_id',
        'headquarter_id',
        'fecha_nacimiento',
        'sueldo',
        'estado',
    ];

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }
    
    public function puesto()
    {
        return $this->belongsTo(Puesto::class);
    }
}
