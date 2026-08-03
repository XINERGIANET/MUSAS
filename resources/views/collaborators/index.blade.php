@extends('template.index')

@section('header')
<h1>Colaboradores</h1>
<p>Lista de Colaboradores</p>
@endsection
@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <!-- Card que contiene el formulario y la tabla -->
    <div class="card shadow">
        <!-- Cuerpo del Card -->
        <div class="card-body">
            <!-- Formulario de Registro -->
            <form id="createCollaboratorForm" class="mb-5" action="{{ route('collaborators.store') }}" method="POST">
                @csrf
                <!-- Fila 1: Nombre, Teléfono y Dirección -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-4">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="nombre" class="form-label mb-0">Nombre</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el nombre" id="nombre" name="nombre" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="telefono" class="form-label mb-0">Teléfono</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el teléfono" id="telefono" name="telefono" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="direccion" class="form-label mb-0">Dirección</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese la dirección" id="direccion" name="direccion" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardar (alineado a la derecha) -->
                <div class="row mb-3">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Registros -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($collaborators as $collaborator)
                        <tr>
                            <td>{{ ($collaborators->currentPage() - 1) * $collaborators->perPage() + $loop->iteration }}</td>
                            <td>{{ $collaborator->nombre }}</td>
                            <td>{{ $collaborator->telefono }}</td>
                            <td>{{ $collaborator->direccion }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal">
                                    Editar
                                </button>
                                <form action="{{ route('collaborators.destroy', $collaborator->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No hay colaboradores registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection