@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('puestos.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('puestos.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Crear Puestos</h1>
<p>Complete el formulario</p>
@endsection

@section('content')

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createRawMaterialForm" action="{{ route('puestos.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
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

@endsection