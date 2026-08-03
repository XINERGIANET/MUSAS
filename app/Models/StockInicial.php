<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInicial extends Model
{
    use HasFactory;
    protected $table = 'stock_inicial';
    protected $fillable = [
        'product_id',
        'headquarter_id',
        'quantity',
    ];

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
