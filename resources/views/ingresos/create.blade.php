@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary active" href="{{ route('ingresos.create') }}?categoria={{ request('categoria') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary" href="{{ route('ingresos.index') }}?categoria={{ request('categoria') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Salidas de 
@if (request('categoria')=='insumos')
Insumos
@elseif (request('categoria')=='industrializados')
Productos industrializados
@endif    
</h2>
<p>Registro de nueva salida</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <form id="ingresosForm" action="{{ route('ingresos.store') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="turno" class="form-label">Turno</label>
                                    <select class="form-control border-dark" id="turno" name="turno" required>
                                        <option value="">Seleccione un turno</option>
                                        <option value="0">Mañana</option>
                                        <option value="1">Tarde</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Producto</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control"
                                            id="busquedaProducto"
                                            placeholder="Buscar producto...">
                                    </div>
                                </div>

                                <!-- <div class="col-md-4">
                                    <label class="form-label">Categoria</label>
                                    <select class="form-select" id="categorias" name="categorias" required>
                                        <option value="">Seleccionar una categoria</option>
                                        @foreach ($categorias as $category)
                                        <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div> -->
                            </div>
                            <hr class="my-2">

                            <div class="table-responsive">
                                <table class="table table-striped" id="ingresosTable">
                                    @php
                                        $colores = ['#e6f7ff', '#fff7e6', '#e6ffe6', '#fde6e6', '#f0e6ff', '#d6f5e6']; // agrega más si tienes más sedes
                                    @endphp
                                    <thead>
                                        <tr>
                                            <th>Categoría</th>
                                            <th>Producto</th>
                                            <th>U. de Medida</th>
                                            <th>Stock</th>
                                            @foreach ($sedes as $index => $sede)
                                                <th style="background-color: {{ $colores[$index % count($colores)] }};">{{ $sede->nombre }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productos as $producto)
                                            @php
                                                $stock = $stocks->where('product_id', $producto->id)->first();
                                            @endphp
                                        <tr>
                                            <td>{{ $producto->category->nombre }}</td>
                                            <td>{{ $producto->nombre }}</td>
                                            <td>{{ $producto->unidad_medida ?? 'N/A' }}</td>
                                            <td>{{ $stock ? $stock->quantity : 0 }}</td>
                                            @foreach ($sedes as $index => $sede)
                                            <td style="background-color: {{ $colores[$index % count($colores)] }};">
                                                <input type="number"
                                                    name="cantidad_{{ $producto->id }}_{{ $sede->id }}"
                                                    class="form-control cantidad-input"
                                                    data-product-id="{{ $producto->id }}"
                                                    data-sede-id="{{ $sede->id }}"
                                                    min="0.01"
                                                    step="0.01"
                                                    placeholder="0.00">
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary" id="saveingresos">Guardar Producción</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
    }

    .cantidad-input {
        width: 100px;
    }
</style>

@endsection
@section('scripts')
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ingresosForm');
        const buttonFiltrar = document.getElementById('saveingresos');


        let clickedFiltrar = false;

        spinner.classList.remove('spinner-visible');
        spinner.classList.add('spinner-hidden');

        buttonFiltrar.addEventListener('click', function() {
            clickedFiltrar = true;
        });

        form.addEventListener('submit', function() {
            if (clickedFiltrar) {
                spinner.classList.remove('spinner-hidden');
                spinner.classList.add('spinner-visible');
            }

            clickedFiltrar = false;
        });
    });

    $('#ingresosForm').on('submit', function(e) {
        e.preventDefault();

        let productos = [];

        $('.cantidad-input').each(function() {
            let cantidad = parseFloat($(this).val());
            let productId = $(this).data('product-id');
            let headquarterId = $(this).data('sede-id');

            if (!isNaN(cantidad) && cantidad > 0) {
                productos.push({
                    product_id: productId,
                    headquarter_id: headquarterId,
                    quantity: cantidad
                });
            }
        });

        // Obtener fecha y hora local del cliente
        let now = new Date();
        let dia = String(now.getDate()).padStart(2, '0');
        let mes = String(now.getMonth() + 1).padStart(2, '0'); // +1 porque enero es 0
        let año = now.getFullYear();
        let horas = String(now.getHours()).padStart(2, '0');
        let minutos = String(now.getMinutes()).padStart(2, '0');
        let segundos = String(now.getSeconds()).padStart(2, '0');

        let fechaHoraCliente = `${año}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;

        let data = {
            _token: $('input[name="_token"]').val(),
            turno: $('#turno').val(),
            client_datetime: fechaHoraCliente,
            products: JSON.stringify(productos)
        };

        $.ajax({
                url: '{{ route('ingresos.store') }}',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.status) {
                        ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Operación exitosa'
                        });
                        window.location.href = '{{ route('ingresos.index') }}?categoria={{ request('categoria') }}';
                    } else {
                        ToastError.fire({
                            text: response.error || 'Ocurrió un error'
                        });
                    }

                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        ToastError.fire({
                            text: 'Ocurrió un error'
                        });
                    } else {
                        ToastError.fire({
                            text: 'Ocurrió un error'
                        });
                    }
                }
            })
            .always(function() {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');
            });
    });

    $(document).on('input', '.cantidad-input', function() {
        let value = $(this).val();

        // Permitir solo números con hasta 2 decimales
        if (!/^\d*(\.\d{0,2})?$/.test(value)) {
            $(this).val(value.slice(0, -1)); // Elimina el último carácter si no es válido
        }
    });

    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('#ingresosTable tbody tr').each(function() {
                var nombre = $(this).find('td:eq(1)').text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    $('#categorias').on('change', function() {
        var presentacionSeleccionada = $(this).val();
        $('#ingresosTable tbody tr').each(function() {
            var presentacionId = $(this).find('td:eq(0)').text().trim();
            // Si no hay selección, mostrar todo
            if (presentacionSeleccionada === "" || $(this).find('td:eq(0)').text().trim() === $('#categorias option:selected').text().trim()) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
</script>
@endsection