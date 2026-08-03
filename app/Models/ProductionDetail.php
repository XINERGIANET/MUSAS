<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id', 
        'product_id',
        'quantity'
    ];

    // Relación con producto terminado
    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
