<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_comprobante',
        'invoice_number',
        'supplier_id',
        'payment_method_id',
        'date',
        'estado'
    ];

    protected $dates = [
        'date'
    ];


    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function getTotalAttribute()
    {
        return $this->details->sum('subtotal');
    }

    // public function rawMaterial()
    // {
    //     return $this->belongsTo(RawMaterial::class);
    // }
}
