<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    protected $table = 'paloteo';
    protected $fillable = [
        'headquarter_id',
        'user_id',
        'product_id',
        'stock_inicial',
        'entradas',
        'salidas',
        'stock_final',
        'venta_teorica',
        'venta_real',
        'turno',
        'fecha',
        'encuadre',
    ];

    protected $dates =[
        'fecha'
    ];

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}