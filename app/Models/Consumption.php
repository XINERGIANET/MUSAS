<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'notes', 'date', 'merma', 'staff_id', 'area', 'estado'];

    // En app/Models/Consumption.php
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
