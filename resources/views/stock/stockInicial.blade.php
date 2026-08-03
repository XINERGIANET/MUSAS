@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif
@endsection

@section('header')
<h2>Ingreso de Stock Inicial</h2>
<p>Ingrese el stock inicial de los productos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <form id="createProductForm" action="{{ route('stock.guardar', ['categoryId' => $categoryId]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="headquarter_id" value="{{ auth()->user()->sede_id }}">
                            <input type="hidden" name="category_id" value="{{ $categoryId }}">

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Producto</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="busquedaProducto" placeholder="Buscar producto...">
                                </div>
                            </div>

                            <div class="table-responsive mb-3" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped" id="paloteoTable">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Stock inicial</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                        <tr class="fila-producto" data-product="{{ $product->id }}">
                                            <td>{{ $product->nombre }}</td>
                                            <td>
                                                <input type="number" name="quantity[{{ $product->id }}]" class="form-control" style="min-width: 3ch;">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('#paloteoTable tbody tr').each(function() {
                var nombre = $(this).find('td').eq(0).text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    $('#createProductForm').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');

        // Filtrar los datos que tienen una cantidad mayor a 0
        var productQuantities = {};

        // Iterar a través de todos los campos de cantidad en el formulario
        $('input[name^="quantity"]').each(function() {
            var productId = $(this).attr('name').match(/\d+/)[0]; // Obtener el product_id de 'quantity[ID]'
            var quantity = $(this).val(); // Obtener el valor de cantidad

            // Solo agregamos el producto si la cantidad es mayor a 0
            if (quantity > 0) {
                productQuantities[productId] = quantity;
            }
        });

        console.log('Datos enviados (solo los productos con cantidad > 0):', productQuantities);

        if (Object.keys(productQuantities).length === 0) {
            ToastError.fire({
                text: 'Debe ingresar cantidades mayores a cero para los productos.'
            });
            return;
        }

        var data = {
            _token: '{{ csrf_token() }}',
            quantity: productQuantities
        };

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                console.log('Respuesta del servidor:', response);

                if (response.success) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message
                    });

                    setTimeout(function() {
                        window.location.href = response.redirectUrl;
                    }, 1000);
                } else {
                    ToastError.fire({
                        text: 'No se pudo guardar stocks'
                    });
                }
            },
            error: function(xhr, status, error) {
        // Aquí accedemos a más detalles del error
        console.log("Estado:", status);
        console.log("Error:", error);
        console.log("Respuesta completa del error:", xhr.responseText);

        ToastError.fire({
            text: 'Ocurrió un error: ' + xhr.responseText  // Muestra el mensaje de error completo
        });
    }
        });
    });
</script>
@endsection