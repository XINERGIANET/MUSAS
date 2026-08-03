<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;
    protected $fillable = [
        'headquarter_id', 
        'fecha',
        'turno'        
    ];

    public function details()
    {
        return $this->hasMany(IngresoDetail::class);
    }

    // Relación con producto terminado
    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
