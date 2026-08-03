@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('suppliers.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('suppliers.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Registrar Proveedores</h2>
<p>Complete el formulario para registrar un nuevo proveedor.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createSupplierForm" action="{{ route('suppliers.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="ruc" class="col-sm-3 col-form-label text-start">RUC</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="ruc" name="ruc" required maxlength="11" onkeypress="isNumber(event)">
                                </div>
                                <label for="razon_social" class="col-sm-3 col-form-label text-start">Razón Social</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="razon_social" name="razon_social" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nombre_comercial" class="col-sm-3 col-form-label text-start">Nombre Comercial</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre_comercial" name="nombre_comercial" required>
                                </div>
                                <label for="tipo" class="col-sm-3 col-form-label text-start">Tipo</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="tipo" name="tipo" required
                                        onchange="toggleDiasPago(this.value)">
                                        <option value="">Seleccione tipo</option>
                                        <option value="C">Contado</option>
                                        <option value="R">Crédito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row d-none" id="diasPagoContainer">
                                <label for="dias_pago" class="col-sm-3 col-form-label text-start">Días para pago</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="dias_pago" name="dias_pago" min="0" required onkeypress="isNumber(event)">
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

@section('scripts')
<script>
       document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createSupplierForm');
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

    function toggleDiasPago(value) {
        const diasPagoContainer = document.getElementById('diasPagoContainer');
        const diasPagoInput = document.getElementById('dias_pago');

        if (value === 'R') {
            diasPagoContainer.classList.remove('d-none');
            diasPagoInput.value = '';
        } else {
            diasPagoContainer.classList.add('d-none');
            diasPagoInput.value = '0';
        }
    }

    // Inicialización: Oculta el campo si ya está seleccionado "Contado"
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        toggleDiasPago(tipoSelect.value);
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
@endsection
