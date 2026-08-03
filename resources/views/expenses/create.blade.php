@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('expenses.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('expenses.index') }}">Historico</a>
    </li>
</ul>

@endsection

@section('header')
    <h2>{{ $title }}</h2>
    <p>{{ $subtitle }}</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h4 class="mb-4">Registrar Gasto</h4>
                    <form action="{{ route('expenses.store') }}" method="POST" id="createExpenseForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_comprobante" class="form-label">Tipo de Comprobante</label>
                                <select class="form-select" id="tipo_comprobante" name="tipo_comprobante" required>
                                    <option value="Factura">Factura</option>
                                    <option value="Boleta">Boleta</option>
                                    <option value="Recibo por honorario">Recibo por honorario</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_number" class="form-label">Número de Comprobante</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number">
                            </div>

                            <div class="col-md-6 mb-3">
                               <label class="form-label">Buscar Proveedor</label>
                                <div class="input-group">
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="">Seleccionar..</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-xs d-none"><i class="ti ti-search"></i></button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="monto" class="form-label">Monto</label>
                                <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="detalle" class="form-label">Detalle</label>
                                <textarea class="form-control" id="detalle" name="detalle" rows="3" required></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method_id" class="form-label">Medio de Pago</label>
                                <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                                    @foreach($metodosPago as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia'))
                            <div class="col-md-6 mb-3">
                                <label for="sede_id" class="form-label">Seleccionar sede</label>
                                <select class="form-select" id="sede_id" name="sede_id">
                                @if($sede)  
                                        <option value="">General</option>  
                                    @foreach($sede as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                @endif
                                </select>
                            </div>
                            @endif

                            @if (auth()->user()->hasRole('adminSede'))
                            <input type="hidden" name="sede_id" value="{{ auth()->user()->sede_id }}">
                            @endif
                            
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary w-100" id="btnGuardar">Guardar Gasto</button>
                            </div>
                        </div>
                    </form>
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
</style>
@endsection

@section('scripts')
    <!-- jQuery y Select2 deben cargarse primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('createExpenseForm');
        const buttonFiltrar = document.getElementById('btnGuardar');
        const spinner = document.getElementById('global-spinner');

        let clickedFiltrar = false;

        spinner.classList.remove('spinner-visible');
        spinner.classList.add('spinner-hidden');

        buttonFiltrar.addEventListener('click', function () {
            clickedFiltrar = true;
        });

        form.addEventListener('submit', function () {
            if (clickedFiltrar) {
                spinner.classList.remove('spinner-hidden');
                spinner.classList.add('spinner-visible');
            }

            clickedFiltrar = false;
        });
    });

        $(document).ready(function() {
            $('#supplier_id').select2({
                theme: 'bootstrap-5',
                minimumInputLength: 1,
                language: 'es',
                ajax: {
                    url: '{{ route('providers.api') }}',
                    dataType: 'json'
                }
            });
        });
    </script>
@endsection

