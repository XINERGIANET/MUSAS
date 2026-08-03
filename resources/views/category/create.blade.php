@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('category.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('category.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')

<h2>Registrar Categoria</h2>
<p>Complete el formulario para registrar una nueva Categoria.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createHeadquarterForm" action="{{ route('category.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-2 col-form-label text-start">Nombre</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required placeholder="Ingrese Nombre">
                                </div>
                                <label for="category_id" class="col-sm-2 col-form-label text-start">Tipo</label>
                                <div class="col-sm-4">
                                    <select class="form-control border-dark" id="category_id" name="category_id">
                                        <option value="">Seleccione un Tipo</option>
                                        @foreach ($type as $type)
                                            <option value="{{ $type->id }}">{{ $type->nombre }}</option>
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
    const form = document.getElementById('createHeadquarterForm');
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
