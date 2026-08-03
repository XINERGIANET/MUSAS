<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $categoryId;

    // Constructor para aceptar parámetros
    public function __construct($categoryId = null)
    {
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        return Product::active()
        ->when($this->categoryId, function ($query, $categoryId) {
            return $query->where('category_id', $categoryId);
        })->get();
    }

    public function map($product): array
    {
        return [
            $product->nombre,
            $product->presentation_id,
            $product->unidad_medida,
            $product->unit_price,
            $product->observacion,
        ];
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Presentación',
            'Unidad de Medida',
            'Precio Unitario',
            'Observación',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], 
        ];
    }
}
