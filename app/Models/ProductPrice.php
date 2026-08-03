<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasFactory;

    protected $table = 'product_price';

    protected $fillable = [
        'product_id',
        'headquarter_id',
        'unit_price',
        'estado',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class, 'headquarter_id');
    }
}
