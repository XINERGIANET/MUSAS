<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_comprobante',
        'invoice_number',
        'payment_method_id',
        'supplier_id',
        'user_id',
        'sede_id',
        'date',
        'turno',
        'description'
    ];

    protected $dates = [
        'date'
    ];

    public function details()
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function sede()
    {
        return $this->belongsTo(Headquarters::class, 'sede_id');
    }

    public function getTotalAttribute()
    {
        return $this->details->sum('subtotal');
    }
}