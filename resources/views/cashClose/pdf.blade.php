<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        CIERRE DE CAJA
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
        <h1>REPORTE GENERAL DE CIERRE DE CAJA</h1>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @if($user)
            <p>Usuario: {{ $user }}</p>
        @endif
        @if($fecha)
            <p>Fecha: {{ $fecha }}</p>
        @endif
        @if($turno)
            <p>Turno: {{ $turno }}</p>
        @endif
        @if($headquarter)
            <p>Sede: {{ $headquarter }}</p>
        @endif
    </div>
        <div class="purchase-block">
            <table class="details-table">
                <thead>
                    <tr>
                        <th colspan="2" class="text-center">Ventas</th>
                    </tr>
                    <tr>
                        <th>Método de pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabla as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Total general -->
        <div class="grand-total" style="position: relative; height: 50px;">
            <div style="position: absolute; left: 0; top: 0; width: 30%; text-align: center; padding: 10px;">
                <div style="font-size: 12px; margin-bottom: 5px;">MONTO</div>
                <div style="font-size: 18px; font-weight: bold;">S/ {{ number_format($monto, 2) }}</div>
            </div>
            <div style="position: absolute; left: 33%; top: 0; width: 30%; text-align: center; padding: 10px; border-left: 2px solid white; border-right: 2px solid white;">
                <div style="font-size: 12px; margin-bottom: 5px;">SISTEMA</div>
                <div style="font-size: 18px; font-weight: bold;">S/ {{ number_format($efectivo, 2) }}</div>
            </div>
            <div style="position: absolute; left: 66%; top: 0; width: 30%; text-align: center; padding: 10px;">
                <div style="font-size: 12px; margin-bottom: 5px;">DIFERENCIA</div>
                <div style="font-size: 18px; font-weight: bold; color: {{ $diferencia >= 0 ? '#90EE90' : '#FFB6C1' }};">
                    S/ {{ number_format($diferencia, 2) }}
                </div>
            </div>
        </div>
</body>
</html>