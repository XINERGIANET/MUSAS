<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage3 extends Model
{
    use HasFactory;

    protected $fillable = [
        'headquarter_id',
        'product_id',
        'quantity',
        'estado'
    ];

    // Relación con producto terminado
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con producto terminado
    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }
}
