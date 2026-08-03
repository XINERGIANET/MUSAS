<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $guarded = [];

     protected $dates = [
        'date'
    ];

    public function usuario()
    {
        return $this->belongsTo( Usuario::class, 'user_id');
    }

}