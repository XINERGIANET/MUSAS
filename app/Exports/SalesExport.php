<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $start_date;
    protected $end_date;
    protected $numero_comprobante;
    protected $nombre_cliente;
    protected $voucher_type;
    protected $turno;
    protected $sede;
    protected $payment_method_id;
    protected $type_sale_arr;

    public function __construct($start_date = null, $end_date = null, $numero_comprobante = null, $nombre_cliente = null, $voucher_type = null, $turno = null, $sede = null, $payment_method_id = null, $type_sale_arr = [])
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->numero_comprobante = $numero_comprobante;
        $this->nombre_cliente = $nombre_cliente;
        $this->voucher_type = $voucher_type;
        $this->turno = $turno;
        $this->payment_method_id = $payment_method_id;
        $this->type_sale_arr = $type_sale_arr;
        $this->sede = $sede;
    }

    public function collection()
    {
       $consulta = Sale::query()
            ->when($this->start_date, fn($query) => $query->whereDate('fecha', '>=', $this->start_date))
            ->when($this->end_date, fn($query) => $query->whereDate('fecha', '<=', $this->end_date))
            ->when($this->sede, fn($query) => $query->where('headquarter_id', $this->sede))
            ->when($this->type_sale_arr, fn($query) => $query->whereIn('type_sale', $this->type_sale_arr))
            ->when($this->numero_comprobante, fn($query) => $query->where('number', 'like', "%{$this->numero_comprobante}%"))
            ->when($this->nombre_cliente, fn($query) => $query->where('cliente', 'like', "%{$this->nombre_cliente}%"))
            ->when($this->voucher_type, fn($query) => $query->where('voucher_type', $this->voucher_type))
            ->when(isset($this->turno), fn($query) => $query->where('turno', $this->turno))
            ->when($this->payment_method_id, function ($query) {
                $query->whereHas('payments', function ($q) {
                    $q->where('payment_method_id', $this->payment_method_id);
                });
            })
            ->orderBy("fecha", "desc")
            ->orderBy("id", "desc");

        return $consulta->get();
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
                $sale->fecha->format('d/m/Y'),                             // Fecha
                $sale->fecha->format('d'),                                 // Día
                $sale->fecha->translatedFormat('F'),                       // Mes completo
                $sale->fecha->format('Y'),                                 // Año
                $sale->fecha->translatedFormat('l'),                       // Día de semana
                $sale->headquarter->nombre ? $sale->headquarter->nombre : '',                        // Sede
                $sale->client_id ? $sale->client->nombre : $sale->cliente,       // Cliente
                $sale->number,                                            // Número
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
            'SEDE',
            'CLIENTE',
            'NÚMERO',
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
