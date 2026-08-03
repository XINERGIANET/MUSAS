<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'insumo_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}