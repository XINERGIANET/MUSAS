<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageInsumo extends Model
{
    use HasFactory;

    protected $table = 'storage_insumos';

    protected $fillable = [
        'insumo_id',
        'quantity',
        'stock_minimo',
        'estado',
    ];
}
