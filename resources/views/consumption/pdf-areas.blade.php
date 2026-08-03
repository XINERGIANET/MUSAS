<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Consumos por Área</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .area-title { background: #e0e0e0; font-weight: bold; padding: 6px; margin-top: 20px; }
        .trabajador-title { background: #f9f9f9; font-weight: bold; padding: 4px; }
    </style>
</head>
<body>
    <h2>Reporte de Consumos por Área</h2>
    <p><strong>Desde:</strong> {{ $filterInfo['startDate'] }} <strong>Hasta:</strong> {{ $filterInfo['endDate'] }}</p>
    @if($filterInfo['area'])
        <p><strong>Área:</strong> {{ $filterInfo['area'] }}</p>
    @endif
    @if($filterInfo['staff_search'])
        <p><strong>Trabajador:</strong> {{ optional(\App\Models\Staff::find($filterInfo['staff_search']))->nombre }}</p>
    @endif
    @if($filterInfo['search'])
        <p><strong>Producto contiene:</strong> {{ $filterInfo['search'] }}</p>
    @endif
    <hr>
    @forelse($resumen as $area => $trabajadores)
        <div class="area-title">Área: {{ $area }}</div>
        @foreach($trabajadores as $staff_id => $productos)
            <div class="trabajador-title">Trabajador: {{ optional(\App\Models\Staff::find($staff_id))->nombre ?? '-' }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($productos as $product_id => $consumos)
                    <tr>
                        <td>{{ optional($consumos->first()->product)->nombre ?? '-' }}</td>
                        <td>{{ $consumos->sum('quantity') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay datos para mostrar.</p>
    @endforelse
</body>
</html>
