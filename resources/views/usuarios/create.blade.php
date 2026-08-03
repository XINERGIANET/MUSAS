@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('usuarios.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('usuarios.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Registrar Usuario</h2>
<p>Complete el formulario para registrar un nuevo usuario.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createUsuarioForm" action="{{ route('usuarios.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                                <label for="email" class="col-sm-3 col-form-label text-start">Email</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="password" class="col-sm-3 col-form-label text-start">Contraseña</label>
                                <div class="col-sm-3">
                                    <input type="password" class="form-control border-dark" id="password" name="password" required>
                                </div>
                                <label for="pin" class="col-sm-3 col-form-label text-start">PIN (opcional)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="pin" name="pin" min="0" max="9999" placeholder="4 dígitos">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="rol_id" class="col-sm-3 col-form-label text-start">Rol</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="rol_id" name="rol_id" required>
                                        @foreach ($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
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
    const form = document.getElementById('createUsuarioForm');
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
