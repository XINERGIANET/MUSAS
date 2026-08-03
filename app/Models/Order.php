<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'table_id',
        'estado', // 'abierto' o 'cerrado'
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function orderdetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function total()
    {
        return $this->orderdetails->sum(function ($detail) {
            return $detail->cantidad * $detail->precio_unitario;
        });
    }
}
