<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category'; // Especifica el nombre de la tabla

    protected $fillable = [
        'id',
        'nombre',
        'estado'
    ];

    // Relación con el modelo Usuario
    public function productos()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}