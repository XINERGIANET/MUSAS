<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Producción</title>
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

        .header p {
            font-size: 12px;
            color: #888;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        .text-center {
            text-align: center;
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

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo ?? 'REPORTE DE PRODUCCIÓN' }}</h1>
        <h2>{{ $subtitulo ?? 'Resumen de Producción' }}</h2>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if($productions->isEmpty())
        <div class="no-data">
            <h3>No hay datos de producción para el período seleccionado.</h3>
        </div>
    @else
        <div class="total-general">
            TOTAL GENERAL: S/ {{ number_format($total, 2) }}
        </div>
        <br>
        <table class="production-table">
            <thead>
                <tr>
                    <th>Sede</th>
                    <th>Producto Terminado</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Subtotal</th>
                    <th>Turno</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productions as $production)
                    @foreach($production->movementDetails as $detail)
                        @php
                            $productSede = $detail->product->productSede->where('headquarter_id', $production->headquarter_id)->first();
                            $precio = $productSede->unit_price ?? $detail->product->unit_price ?? 0;
                            $subtotal = $precio * $detail->quantity;
                        @endphp
                        <tr>
                            <td>{{ $production->headquarter->nombre ?? '—' }}</td>
                            <td>{{ $detail->product->nombre ?? 'Producto desconocido' }}</td>
                            <td class="text-right">{{ number_format($detail->quantity, 2) }}</td>
                            <td class="text-right">S/ {{ number_format($precio, 2) }}</td>
                            <td class="text-right">S/ {{ number_format($subtotal, 2) }}</td>
                            <td>{{ $production->turno === 0 ? 'Mañana' : 'Tarde' }}</td>
                            <td>{{ \Carbon\Carbon::parse($production->date)->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>