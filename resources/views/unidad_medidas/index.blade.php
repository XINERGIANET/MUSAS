@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('unidad_medidas.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('unidad_medidas.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Unidades de Medida</h1>
<p>Lista de unidades de medida</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row mb-3">
                        <!-- Filtro de búsqueda a la izquierda -->
                        <!-- <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Unidad de Medida...">
                            </div>
                        </div> -->

                        <!-- Botones de Importar y Exportar alineados a la derecha -->
                        <!-- <div class="col-md-6 offset-md-2 text-end">
                            <button class="btn btn-success me-2" type="button" id="btnImportar">
                                <i class="fas fa-file-import"></i> Importar
                            </button>
                            <button class="btn btn-info" type="button" id="btnExportar">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div> -->
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($unidadMedidas as $unidadMedida)
                                <tr>
                                    <td>{{ ($unidadMedidas->currentPage() - 1) * $unidadMedidas->perPage() + $loop->iteration }}</td>
                                    <td>{{ $unidadMedida->nombre }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $unidadMedida->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $unidadMedida->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de Editar -->
                                <div class="modal fade" id="editModal{{ $unidadMedida->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $unidadMedida->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('unidad_medidas.update', $unidadMedida->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel{{ $unidadMedida->id }}">Editar Sede</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="edit_nombre{{ $unidadMedida->id }}">Nombre</label>
                                                        <input type="text" class="form-control" id="edit_nombre{{ $unidadMedida->id }}" name="nombre" value="{{ $unidadMedida->nombre }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal de Eliminar -->
                                <div class="modal fade" id="deleteModal{{ $unidadMedida->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $unidadMedida->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('unidad_medidas.destroy', $unidadMedida->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $unidadMedida->id }}">Eliminar Unidad de Medida</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>¿Estás seguro de que deseas eliminar esta unidad de medida?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay sedes registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $unidadMedidas->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection