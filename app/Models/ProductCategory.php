<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';

    protected $fillable = [
        'category_id',
        'nombre',
        'estado'
    ];

    public function productos()
    {
        return $this->hasMany(Product::class, 'product_categorie_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
