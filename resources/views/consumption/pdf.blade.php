<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consumo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .filter-info h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 5px;
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
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            text-align: right;
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Consumos</h2>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if(isset($filterInfo))
    <div class="filter-info">
        <h4>Filtros Aplicados:</h4>
        <div class="filter-row">
            @if($filterInfo['startDate'])
            <div class="filter-item">
                <span class="filter-label">Fecha Inicial:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filterInfo['startDate'])->format('d/m/Y') }}</span>
            </div>
            @endif
            
            @if($filterInfo['endDate'])
            <div class="filter-item">
                <span class="filter-label">Fecha Final:</span>
                <span class="filter-value">{{ \Carbon\Carbon::parse($filterInfo['endDate'])->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
        
        <div class="filter-row">
            @if($filterInfo['search'])
            <div class="filter-item">
                <span class="filter-label">Materia Prima:</span>
                <span class="filter-value">{{ $filterInfo['search'] }}</span>
            </div>
            @endif
            
            @if($filterInfo['staff_search'])
            <div class="filter-item">
                <span class="filter-label">Encargado:</span>
                <span class="filter-value">{{ $filterInfo['staff_name'] ?? 'ID: ' . $filterInfo['staff_search'] }}</span>
            </div>
            @endif
        </div>
        
        @if(isset($filterInfo['merma']) && $filterInfo['merma'] == 1)
        <div class="filter-row">
            <div class="filter-item">
                <span class="filter-label">Tipo:</span>
                <span class="filter-value">Solo Merma</span>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($consumptions->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Materia Prima</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
                <th>Encargado</th>
                <th>Área</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumptions as $consumption)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($consumption->date)->format('d/m/Y') }}</td>
                    <td>
                        @if($consumption->product)
                        {{ $consumption->product->nombre }}
                        @else
                        N/A
                        @endif
                    </td>
                    <td>{{ $consumption->quantity }}</td>
                    <td>S/ {{ number_format($consumption->product->unit_price, 2) }}</td>
                    <td>S/ {{ number_format($consumption->quantity * $consumption->product->unit_price, 2) }}</td>
                    <td>{{ $consumption->staff->nombre ?? 'N/A' }}</td>
                    <td>{{ $consumption->area ?? '-' }}</td>
                    <td>{{ $consumption->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total-row">
        <strong>TOTAL: S/ {{ number_format($total, 2) }}</strong>
    </div>
    @else
    <div class="no-data">
        <p>No se encontraron registros con los filtros aplicados.</p>
    </div>
    @endif
</body>
</html>