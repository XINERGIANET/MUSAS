<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $start_date;
    protected $end_date;

    public function __construct($start_date = null, $end_date = null)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $query = Purchase::with(['supplier', 'details.product','paymentMethod']);

        if ($this->start_date) {
            $query->whereDate('date', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('date', '<=', $this->end_date);
        }

        return $query->get();
    }

    public function map($sale): array
    {
        // $payment_methods = $sale->payments->map(function ($p) {
        //     return strtoupper(optional($p->payment_method)->name);
        // })->unique()->implode(', ');

        // // Serie y correlativo separados del campo number (ej. "CI01-00000123")
        // $serie = explode('-', $sale->number)[0] ?? '';
        // $correlativo = explode('-', $sale->number)[1] ?? '';

        $data = [];

        foreach ($sale->details as $detail) {
            $data[] = [
                $sale->date->format('d/m/Y'),                             // Fecha
                $sale->date->format('d'),                                 // Día
                $sale->date->translatedFormat('F'),                       // Mes completo
                $sale->date->format('Y'),                                 // Año
                $sale->date->translatedFormat('l'),                       // Día de semana
                optional($sale->supplier)->razon_social,           // Proveedor
                optional($sale->supplier)->ruc,                    // RUC
                $sale->tipo_comprobante,                                  // Tipo comprobante
                $sale->invoice_number,                                    // Número
                $sale->paymentMethod->nombre,                             // Forma de pago
                $sale->total,                                             // Total
                optional($detail->product)->nombre,                // Artículo
                'UND',                                                    // Unidad
                $detail->quantity,                                        // Cantidad
                $detail->unit_price,                                      // Precio unitario
                $detail->subtotal                                         // Subtotal venta
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'FECHA',
            'DIA',
            'MES',
            'AÑO',
            'DIA DE SEMANA',
            'PROVEEDOR',
            'RUC',
            'COMPROBANTE',
            'NÚMERO',
            'FORMA DE PAGO',
            'TOTAL',
            'ARTÍCULO',
            'UNIDAD',
            'CANTIDAD',
            'PRECIO UNITARIO',
            'SUBTOTAL COMPRA'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]]
        ];
    }
}
