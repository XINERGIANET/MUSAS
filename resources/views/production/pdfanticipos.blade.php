<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Producción con Anticipos</title>
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
            margin: 20px 0 10px 0;
            border-radius: 5px;
        }

        .section-title.anticipadas, .section-title.delivery {
            background-color: #FF9800;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

        .total-section {
            background-color: #f5f5f5;
            padding: 10px 15px;
            text-align: right;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 3px;
            border-left: 4px solid #2196F3;
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

        .section-no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
            background-color: #f9f9f9;
            border-radius: 3px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE PRODUCCIÓN GENERAL</h1>
        <h2>Producciones Generales</h2>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @php
        // Separar producciones por tipo
        $produccionesNormales = $productions->where('tipo', 6);
        $produccionesAnticipadas = $productions->where('tipo', 8);
        $produccionesDelivery = $productions->where('tipo', 9);
        
        // Calcular totales por sección
        $totalNormales = $produccionesNormales->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $precio = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first()->unit_price ?? $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });
        
        $totalAnticipadas = $produccionesAnticipadas->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $precio = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first()->unit_price ?? $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });

        $totalDelivery = $produccionesDelivery->sum(function ($production) {
            return $production->movementDetails->sum(function ($detail) use ($production) {
                $precio = $detail->product->productSede
                    ->where('headquarter_id', $production->headquarter_id)
                    ->first()->unit_price ?? $detail->product->unit_price;
                return $detail->quantity * $precio;
            });
        });
    @endphp

    @if($productions->isEmpty())
        <div class="no-data">
            <h3>No hay datos de producción para el período seleccionado.</h3>
        </div>
    @else
        <div class="total-general">
            TOTAL GENERAL: S/ {{ number_format($total, 2) }}
        </div>

        <!-- SECCIÓN: PRODUCCIONES NORMALES -->
        <div class="section-title">
             PRODUCCIONES NORMALES
        </div>

        @if($produccionesNormales->isEmpty())
            <div class="section-no-data">
                No hay producciones normales en el período seleccionado.
            </div>
        @else
            <table class="production-table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Producto Terminado</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Subtotal</th>
                        <th>Turno</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produccionesNormales as $production)
                        @foreach($production->movementDetails as $detail)
                            @php
                                $precio = $detail->product->productSede
                                    ->where('headquarter_id', $production->headquarter_id)
                                    ->first()->unit_price ?? $detail->product->unit_price;
                            @endphp
                            <tr>
                                <td>{{ $production->headquarter->nombre }}</td>
                                <td>{{ $detail->product->nombre }}</td>
                                <td class="text-right">{{ number_format($detail->quantity, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio * $detail->quantity, 2) }}</td>
                                <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                <td>{{ date('d/m/Y', strtotime($production->date)) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            
            <div class="total-section">
                Subtotal Producciones Normales: S/ {{ number_format($totalNormales, 2) }}
            </div>
        @endif

        <!-- SECCIÓN: PRODUCCIONES ANTICIPADAS -->
        <div class="section-title anticipadas">
             PRODUCCIONES ANTICIPADAS
        </div>

        @if($produccionesAnticipadas->isEmpty())
            <div class="section-no-data">
                No hay producciones anticipadas en el período seleccionado.
            </div>
        @else
            <table class="production-table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Producto Terminado</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Subtotal</th>
                        <th>Turno</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produccionesAnticipadas as $production)
                        @foreach($production->movementDetails as $detail)
                            @php
                                $precio = $detail->product->productSede
                                    ->where('headquarter_id', $production->headquarter_id)
                                    ->first()->unit_price ?? $detail->product->unit_price;
                            @endphp
                            <tr>
                                <td>{{ $production->headquarter->nombre }}</td>
                                <td>{{ $detail->product->nombre }}</td>
                                <td class="text-right">{{ number_format($detail->quantity, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio * $detail->quantity, 2) }}</td>
                                <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                <td>{{ date('d/m/Y', strtotime($production->date)) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            
            <div class="total-section">
                Subtotal Producciones Anticipadas: S/ {{ number_format($totalAnticipadas, 2) }}
            </div>
        @endif

        <!-- SECCIÓN: PRODUCCIONES DELIVERY -->
        <div class="section-title delivery">
             PRODUCCIONES DELIVERY
        </div>

        @if($produccionesDelivery->isEmpty())
            <div class="section-no-data">
                No hay producciones delivery en el período seleccionado.
            </div>
        @else
            <table class="production-table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Producto Terminado</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Subtotal</th>
                        <th>Turno</th>
                        <th>Registrado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produccionesDelivery as $production)
                        @foreach($production->movementDetails as $detail)
                            @php
                                $precio = $detail->product->productSede
                                    ->where('headquarter_id', $production->headquarter_id)
                                    ->first()->unit_price ?? $detail->product->unit_price;
                            @endphp
                            <tr>
                                <td>{{ $production->headquarter->nombre }}</td>
                                <td>{{ $detail->product->nombre }}</td>
                                <td class="text-right">{{ number_format($detail->quantity, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio, 2) }}</td>
                                <td class="text-right">S/ {{ number_format($precio * $detail->quantity, 2) }}</td>
                                <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                <td>{{ date('d/m/Y', strtotime($production->date)) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            
            <div class="total-section">
                Subtotal Producciones Delivery: S/ {{ number_format($totalDelivery, 2) }}
            </div>
        @endif
    @endif
</body>
</html>
