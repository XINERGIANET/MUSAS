<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SaleDetail extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'sale_id',
        'quantity',
        'unit_price',
        'subtotal',
        'estado',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::updated(function ($detail) {
            try {
                Bitacora::create([
                    'user_id' => auth()->user()->id,
                    'action' => 'UPDATE',
                    'table' => $detail->getTable(),
                    'date' => now(),
                    'before' =>json_encode($detail->getOriginal()),
                    'after' => json_encode($detail->getChanges())
                ]);
            }catch (\Exception $e) {
                Log::error('Error al registrar en la bitácora: ' . $e->getMessage());
            }
        });

        static::deleted(function ($detail) {
            try {
                Bitacora::create([
                    'user_id' => auth()->user()->id,
                    'action' => 'DELETE',
                    'table' => $detail->getTable(),
                    'date' => now(),
                    'before' => json_encode([
                        'product_id' => $detail->product_id,
                        'sale_id' => $detail->sale_id
                    ])  // Registrar los datos antes de eliminar
                ]);
            } catch (\Exception $e) {
                Log::error('Error al registrar en la bitácora (DELETE): ' . $e->getMessage());
            }
        });
    }

}