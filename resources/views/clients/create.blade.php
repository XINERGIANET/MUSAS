@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('clients.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('clients.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')

<h2>Registrar Cliente</h2>
<p>Complete el formulario para registrar un nuevo cliente.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createClientForm" action="{{ route('clients.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="ruc_dni" class="col-sm-3 col-form-label text-start">RUC/DNI</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="ruc_dni" name="ruc_dni" required maxlength="11" onkeypress="isNumber(event)">
                                </div>
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre Completo / Razón Social</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="telefono" class="col-sm-3 col-form-label text-start">Teléfono</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="telefono" name="telefono" required maxlength="9" onkeypress="isNumber(event)">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
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
</style>
@endsection
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createClientForm');
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

    //Number/Decimal
    function isNumber(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;
        if (charCode < 48 || charCode > 57) {
            evt.preventDefault();
            return false;
        }
        return true;
    }

    function isDecimal(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;
        if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
            var input = evt.target || evt.srcElement;
            if (charCode === 46 && input.value.includes('.')) {
                evt.preventDefault();
                return false;
            }
            return true;
        } else {
            evt.preventDefault();
            return false;
        }
    }
</script>
