<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén</title>
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
    <h2>STOCK de Materia Prima</h2>
    <h3>Valorizado: S/ {{ number_format($valorizado,2) }} </h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($storages as $storage)
                <tr>
                    <td>{{ $storage->product->nombre }}</td>
                    <td>{{ number_format($storage->quantity, 2) }}</td>
                    <td>{{ number_format($storage->product->unit_price,2) }}</td>
                    <td>{{ number_format($storage->quantity * $storage->product->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
