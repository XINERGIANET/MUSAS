<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingreso_id', 
        'product_id',
        'quantity'
    ];

    // Relación con producto terminado
    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
