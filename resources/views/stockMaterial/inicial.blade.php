@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif
@endsection

@section('header')
<h2>Ingreso de Stock Inicial</h2>
<p>Ingrese el stock inicial</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <form id="createProductForm" action="{{ route('stockMaterial.guardar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="headquarter_id" value="{{ auth()->user()->sede_id }}">

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Producto</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        id="busquedaProducto"
                                        placeholder="Buscar producto...">
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
                                                <input type="number"
                                                    name="quantity[{{ $product->id }}]"
                                                    class="form-control"
                                                    style="min-width: 3ch;">
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
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                if (response.success) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: 'Stocks guardados correctamente.'
                    });
                    location.reload();
                } else {
                    ToastError.fire({
                        text: 'No se pudo guardar stocks'
                    });
                }
            },
            error: function(xhr) {
                ToastError.fire({
                    text: 'Ocurrio un error'
                });
            }
        });
    });
</script>
@endsection