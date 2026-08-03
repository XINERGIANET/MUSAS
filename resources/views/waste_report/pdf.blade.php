<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Merma</title>
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
    <h2>Reporte de Merma</h2>
     <table class="table table-bordered table-striped" id="purchaseTable">
        <thead class="table">
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $movement)
                @php
                    $firstDetail = $movement->movementDetails->first();
                @endphp
                <tr>
                    <td>{{ $firstDetail && $firstDetail->product ? $firstDetail->product->nombre : 'Sin producto' }}</td>
                    <td>{{ $firstDetail ? $firstDetail->quantity : 'Sin cantidad' }}</td>
                    <td>{{ $movement->date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
