<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Resumen de Producción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }

        .section-title {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 5px 0;
            border-radius: 5px;
        }

        .section-title.anticipada {
            background-color: #FF9800;
        }

        .section-title.delivery {
            background-color: #9C27B0;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .production-table th {
            background-color: #2196F3;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .production-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        .production-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            background-color: #f5f5f5;
            padding: 10px 15px;
            text-align: right;
            font-weight: bold;
            margin-bottom: 5px;
            border-radius: 3px;
            border-left: 4px solid #2196F3;
        }

        .total-section.anticipada {
            border-left-color: #FF9800;
        }

        .total-section.delivery {
            border-left-color: #9C27B0;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        .total-general {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    @php
        $tipos = [
            'normal' => ['label' => 'Producción Directa', 'class' => ''],
            'anticipada' => ['label' => 'Producción Anticipada', 'class' => 'anticipada'],
            'delivery' => ['label' => 'Producción Delivery', 'class' => 'delivery']
        ];

        // Calcular el total general antes de imprimirlo
        $totalGeneral = 0;
        foreach ($tipos as $key => $tipo) {
            if (!empty($resumen[$key])) {
                foreach ($resumen[$key] as $data) {
                    $totalGeneral += $data['subtotal'];
                }
            }
        }
    @endphp

    <div class="header">
        <h1>RESUMEN DE PRODUCCIÓN</h1>
        <h2>Periodo: {{ $filterInfo['start_date'] ?? '-' }} a {{ $filterInfo['end_date'] ?? '-' }}</h2>
        <p>
            @if(!empty($filterInfo['headquarter']))
                <strong>Sede:</strong> {{ $filterInfo['headquarter'] }} &nbsp;
            @endif
            @if(!empty($filterInfo['turno']) || $filterInfo['turno'] === 0)
                <strong>Turno:</strong> {{ $filterInfo['turno'] == 0 ? 'Mañana' : 'Tarde' }}
            @endif
        </p>
    </div>

    {{-- Mostrar total general calculado --}}
    <div class="total-general">
        TOTAL GENERAL: S/ {{ number_format($totalGeneral, 2) }}
    </div>

    {{-- Mostrar las tablas por tipo --}}
    @foreach($tipos as $key => $tipo)
        <div class="section-title {{ $tipo['class'] }}">
            {{ strtoupper($tipo['label']) }}
        </div>

        @php 
            $granTotal = 0;
            if (!empty($resumen[$key])) {
                foreach ($resumen[$key] as $data) {
                    $granTotal += $data['subtotal'];
                }
            }
        @endphp

        <div class="total-section {{ $tipo['class'] }}">
            TOTAL {{ strtoupper($tipo['label']) }}: S/ {{ number_format($granTotal, 2) }}
        </div>

        <table class="production-table">
            <thead>
                <tr>
                    <th>Producto Terminado</th>
                    <th class="text-right">Total (unid)</th>
                    <th class="text-right">Total (soles)</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($resumen[$key]))
                    @foreach($resumen[$key] as $data)
                        <tr>
                            <td>{{ $data['nombre'] }}</td>
                            <td class="text-right">{{ number_format($data['cantidad'], 2) }}</td>
                            <td class="text-right">{{ number_format($data['subtotal'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="no-data">Sin datos para esta sección</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endforeach
</body>

</html>