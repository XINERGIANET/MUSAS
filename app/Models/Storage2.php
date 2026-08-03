<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage2 extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'estado'
    ];

    // Relación con el modelo Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
