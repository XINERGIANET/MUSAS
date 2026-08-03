<?php

namespace App\Models;

use App\Http\Controllers\HeadquartersController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_id',
        'staff_id',
        'quantity',
        'transformado',
        'unit_price',
        'estado'
        ];
    
    public $timestamps = false;

    // Relación con movimiento
    public function movement()
    {
        return $this->belongsTo(Movement::class , 'movement_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class , 'staff_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
