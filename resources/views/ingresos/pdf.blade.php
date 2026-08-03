<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Lista de Ingresos</h2>
    <table>
        <thead>
            <tr>
                <th>Sede</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Turno</th>
                <th>Registrado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productions as $production)
                @foreach($production->movementDetails as $detail)
                    <tr>
                        <td>{{ $production->headquarter->nombre }}</td>
                        <td>{{ $detail->product->nombre }}</td>
                        <td>{{ number_format($detail->quantity, 2) }}</td>
                        <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                        <td>{{ $production->date }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
