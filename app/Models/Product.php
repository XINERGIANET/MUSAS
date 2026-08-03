<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'category_id',
        'presentation_id',
        'observacion',
        'unidad_medida',
        'unit_price',
        'estado',
        'turno',
        'product_categorie_id',
        'created_at'
    ];

    public function scopeActive($query){
        return $query->where('estado', 0);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function supplierDos()
    {
        return $this->belongsTo(Supplier::class, 'supplier2_id');
    }

    public function presentation()
    {
        return $this->belongsTo(Presentation::class);
    }

    public function movements()
    {
        return $this->hasMany(MovementDetail::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_categorie_id');
    }

        // Nueva relación con producción
    public function productionDetails() {
        return $this->hasMany(ProductionDetail::class);
    }

        // Nueva relación con almacenamiento
    public function storage3s() {
        return $this->hasMany(Storage3::class);
    }

    public function productProvider()
    {
        return $this->hasMany(ProductProvider::class);
    }

    public function productSede()
    {
        return $this->hasMany(ProductPrice::class);
    }
}
