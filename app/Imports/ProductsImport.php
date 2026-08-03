<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;


class ProductsImport implements ToModel, WithStartRow
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function model(array $row)
    {
        if (empty($row[0])) {
            return null;
        }
        
        return new Product([
            'nombre' => $row[0],
            'unidad_medida' => $row[1],
            'unit_price' => $row[2],
            'observacion' => !empty($row[3]) ? $row[3] : null,
            'category_id' => $this->categoryId, 
            'estado' => 0,
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }
}
