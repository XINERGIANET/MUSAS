<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method_id',
        'headquarter_id',
        'turno',
        'usuario_id',
        'fecha',
        'monto'
    ];

    // En app/Models/Consumption.php
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

}
