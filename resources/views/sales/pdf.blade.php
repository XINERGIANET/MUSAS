<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        REPORTE DE VENTAS
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .filters {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        
        .purchase-block {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .purchase-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .supplier-info {
            background-color: #f1f3f4;
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .details-table th {
            background-color: #6c757d;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .details-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #eee;
        }
        
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .purchase-total {
            background-color: #e9ecef;
            padding: 8px 10px;
            text-align: right;
            font-weight: bold;
            color: #495057;
        }
        
        .grand-total {
            margin-top: 20px;
            text-align: center;
            background-color: #28a745;
            color: white;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE GENERAL DE VENTAS</h1>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @php
            $applied = collect($filters)->filter(fn($v) => !is_null($v) && $v !== '');
        @endphp

        @forelse($applied as $key => $value)
            <div>
                {{ $key }}: {{ $value }}
            </div>
        @empty
            <div>Todas las ventas</div>
        @endforelse
    </div>

    @if($sales->count() > 0)
            <div class="purchase-block">

                
                <!-- Detalles de la compra -->
                @if($sales->count() > 0)
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th>N° comprobante</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Fecha entrega</th>
                                <th>Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->number }}</td>
                                    <td>
                                        @if($sale->type_sale == 0 && $sale->restaurant == 1)
                                            Restaurante
                                        @elseif($sale->type_sale == 0)
                                            Directa
                                        @elseif($sale->type_sale == 1)
                                            Anticipada
                                        @elseif($sale->type_sale == 2 || $sale->type_sale == 3)
                                            Delivery
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $sale->cliente ?? $sale->client->nombre ?? "varios" }}</td>
                                    <td>{{ $sale->fecha }}</td>
                                    <td>{{ $sale->total }}</td>
                                    <td>{{ $sale->saldo() }}</td>
                                    <td>{{ $sale->fecha_entrega ? $sale->fecha_entrega : "N/A" }}</td>
                                    <td>{{ $sale->headquarter->nombre ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 10px; color: #6c757d; font-style: italic;">
                        Sin ventas registrados
                    </div>
                @endif
                

            </div>

        <!-- Total general -->
        <div class="grand-total">
            TOTAL GENERAL DE VENTAS: S/ {{ number_format($totalGeneral, 2) }}
        </div>
    @else
        <div class="no-data">
            <h3>No se encontraron ventas</h3>
            <p>No hay ventas que coincidan con los filtros aplicados.</p>
        </div>
    @endif
</body>
</html>