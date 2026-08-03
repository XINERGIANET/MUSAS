<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'headquarter_id', 
        'fecha',
        'turno'        
    ];

    public function details()
    {
        return $this->hasMany(ProductionDetail::class);
    }

    // Relación con producto terminado
    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
