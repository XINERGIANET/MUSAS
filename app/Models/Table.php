<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 
        'status', 
        'opened_at'
    ];

    public function sale()
    {
        return $this->hasOne(Sale::class)->where('estado', 0); // solo venta activa
    }

    public function order()
    {
        return $this->hasOne(Order::class)->where('estado', 'abierto');
    }
}
