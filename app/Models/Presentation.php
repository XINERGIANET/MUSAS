<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentation extends Model
{
    protected $table = 'presentation'; // Especifica el nombre de la tabla

    protected $fillable = [
        'id',
        'nombre',
        'estado'
    ];

    // Relación con el modelo Usuario
    public function productos()
    {
        return $this->hasMany(Product::class, 'presentation_id');
    }
}