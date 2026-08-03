<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_sale',
        'user_id',
        'voucher_type',
        'voucher_id',
        'voucher_file',
        'number',
        'headquarter_id',
        'fecha',
        'client_id',
        'cliente',
        'telefono',
        'total',
        'fecha_entrega',
        'hora_entrega',
        'direccion',
        'referencia',
        'observacion',
        'foto',
        'turno',
        'estado',
        'status',
        'table_id',
        'sede_recojo',
        'restaurant',
    ];

     protected $dates = [
        'fecha'
    ];

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function saldo()//saldo a pagar de una venta
    {
        $totalPagos = $this->payments()->sum('monto');
        $saldo = $this->total - $totalPagos;
        return number_format($saldo, 2, '.', '');
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function sedeRecojo()
    {
        return $this->belongsTo(Headquarters::class, 'sede_recojo');
    }

    public function usuario()
    {
        return $this->belongsTo( Usuario::class, 'user_id');
    }

    protected static function booted()
    {
        static::updated(function ($sale) {
            try {

                $dirtyAttributes = $sale->getDirty(); // Atributos que han cambiado
                $originalValues = [];

                foreach ($dirtyAttributes as $key => $value) {
                    $originalValues[$key] = $sale->getOriginal($key); // Valores originales de los atributos que cambiaron
                }

                Bitacora::create([
                    'user_id' => auth()->user()->id,
                    'action' => 'UPDATE',
                    'table' => $sale->getTable(),
                    'date' => now(),
                    'before' =>json_encode($sale->getOriginal()),
                    'after' => json_encode($sale->getChanges())
                ]);
            }catch (\Exception $e) {
                Log::error('Error al registrar en la bitácora: ' . $e->getMessage());
            }
        });
    }
}