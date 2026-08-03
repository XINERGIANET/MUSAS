<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cashClose extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'monto',
        'turno',
        'usuario_id',
        'headquarter_id',
        'estado'
    
    ];

    // En app/Models/Consumption.php
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class, 'headquarter_id');
    }

}
