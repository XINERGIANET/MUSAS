<?php

namespace App\Models;

use App\Http\Controllers\HeadquartersController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'headquarter_id',
        'headquarter_to_id',
        'date',
        'tipo',
        'estado',
        'turno',
    ];

    // Relación con los detalles de la movimiento
    public function movementDetails()
    {
        return $this->hasMany(MovementDetail::class, 'movement_id');
    }

    // app/Models/Movement.php

    public function headquarter()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function headquarter_to()
    {
        return $this->belongsTo(Headquarters::class, 'headquarter_to_id');
    }


}
