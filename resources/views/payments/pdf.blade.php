<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        REPORTE DE PAGOS
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
        <h1>REPORTE GENERAL DE PAGOS</h1>
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
            <div>Todos los pagos</div>
        @endforelse
    </div>

    @if($payments->count() > 0)
            <div class="purchase-block">

                
                <!-- Detalles de la compra -->
                @if($payments->count() > 0)
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th>Mét. de pago</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Turno</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->paymentMethod->nombre }}</td>
                                    <td>{{ $payment->fecha->format('d/m/Y') }}</td>
                                    <td>{{ number_format($payment->monto,2) }}</td>
                                    <td>{{ $payment->turno === 0 ? 'Mañana' : ( $payment->turno === 1 ? 'Tarde' : '-') }}</td>
                                    <td>{{ $payment->usuario ? $payment->usuario->email : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 10px; color: #6c757d; font-style: italic;">
                        Sin pagos registrados
                    </div>
                @endif
                

            </div>

        <!-- Total general -->
        <!-- <div class="grand-total">
            TOTAL GENERAL DE PAGOS: S/ {{-- number_format($totalGeneral, 2) --}}
        </div> -->
    @else
        <div class="no-data">
            <h3>No se encontraron pagos</h3>
            <p>No hay pagos que coincidan con los filtros aplicados.</p>
        </div>
    @endif
</body>
</html>