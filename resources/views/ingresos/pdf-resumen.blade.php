<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Consumos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        .filter-info h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 16px;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 8px;
        }
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        .filter-label {
            font-weight: bold;
            color: #6c757d;
        }
        .filter-value {
            color: #212529;
            margin-left: 5px;
        }
        .resumen-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .resumen-table th {
            background-color: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #0056b3;
        }
        .resumen-table td {
            border: 1px solid #dee2e6;
            padding: 10px 8px;
            text-align: left;
            font-size: 12px;
        }
        .resumen-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .resumen-table tbody tr:hover {
            background-color: #e3f2fd;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 25px;
            text-align: right;
            padding: 15px;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            font-style: italic;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .no-data h3 {
            color: #dc3545;
            margin-bottom: 10px;
        }
        .empty-total {
            text-align: center;
            padding: 20px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Resumen de Consumos por Producto</h2>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if(isset($filterInfo))
    <div class="filter-info">
        <h4>Filtros Aplicados:</h4>
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">Fecha Inicial:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filterInfo['startDate'])->format('d/m/Y') }}</span>
            </div>
            
            <div class="filter-item">
                <span class="filter-label">Fecha Final:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filterInfo['endDate'])->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($resumen->count() > 0)
    <table class="resumen-table">
        <thead>
            <tr>
                <th style="width: 50%;">Producto</th>
                <th style="width: 15%; text-align: center;">Cantidad Total</th>
                <th style="width: 15%; text-align: right;">Precio Unit.</th>
                <th style="width: 20%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumen as $item)
                <tr>
                    <td>{{ $item['producto'] }}</td>
                    <td style="text-align: center;">{{ number_format($item['cantidad_total'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item['precio_unitario'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($item['subtotal'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-amount">
            TOTAL GENERAL: S/ {{ number_format($total, 2) }}
        </div>
    </div>
    @else
    <div class="no-data">
        <h3>Sin datos</h3>
        <p>No se encontraron registros de consumo con los filtros aplicados.</p>
    </div>
    
    <div class="empty-total">
        <strong>TOTAL: S/ {{ number_format($total ?? 0, 2) }}</strong>
    </div>
    @endif
</body>
</html>