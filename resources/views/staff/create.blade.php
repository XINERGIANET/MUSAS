@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('staff.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('staff.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Registrar Personal</h2>
<p>Complete el formulario para registrar un nuevo personal.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createStaffForm" action="{{ route('staff.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="dni" class="col-sm-3 col-form-label text-start">DNI</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="dni" name="dni" required>
                                </div>
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombres y Apellidos</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="telefono" class="col-sm-3 col-form-label text-start">Teléfono</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="telefono" name="telefono" required>
                                </div>
                                <label for="puesto" class="col-sm-3 col-form-label text-start">Puesto</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="puesto" name="puesto" required>
                                        <option value="">Seleccione un puesto</option>
                                        @foreach ($puestos as $puesto)
                                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-start">Sede</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="headquarter_id" name="headquarter_id" required>
                                        <option value="">Seleccione una sede</option>
                                        @foreach ($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="fecha_nacimiento" class="col-sm-3 col-form-label text-start">Fecha de Nacimiento</label>
                                <div class="col-sm-3">
                                    <input type="date" class="form-control border-dark" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="sueldo" class="col-sm-3 col-form-label text-start">Sueldo</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="sueldo" name="sueldo" step="0.01" min="0" placeholder="0.00" required>
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
    const form = document.getElementById('createStaffForm');
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
</script>
@endsection