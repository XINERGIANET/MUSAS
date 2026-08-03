<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'monto',
        'payment_method_id',
        'fecha',
        'estado',
        'turno',
        'user_id',
    ];

    protected $dates = [
        'fecha'
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class,'user_id');
    }

    public function getHeadquarterAttribute()   
    {
        if ($this->sale && in_array($this->sale->type_sale, [0, 1])) { // solo jala sede para directas y anticipadas, delivery que tenga sede null
            return $this->sale->headquarter;
        }
        return null;
    }

    protected static function booted()
    {
        static::updated(function ($payment) {
            try {
                Bitacora::create([
                    'user_id' => auth()->user()->id,
                    'action' => 'UPDATE',
                    'table' => $payment->getTable(),
                    'date' => now(),
                    'before' =>json_encode($payment->getOriginal()),
                    'after' => json_encode($payment->getChanges())
                ]);
            }catch (\Exception $e) {
                Log::error('Error al registrar en la bitácora: ' . $e->getMessage());
            }
        });
    }

}