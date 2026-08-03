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
<h1>Puestos</h1>
<p>Lista de Puestos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">

                    <div class="row mb-3">
                        <!-- Filtro de búsqueda a la izquierda -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text"
                                    class="form-control"
                                    id="textoBusqueda"
                                    placeholder="Buscar Nombre Puesto...">
                            </div>
                        </div>

                        
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
                                @forelse ($puestos as $puesto)
                                <tr>
                                    <td>{{ ($puestos->currentPage() - 1) * $puestos->perPage() + $loop->iteration }}</td>
                                    <td>{{ $puesto->nombre }}</td>
                                    <td>
                                        <!--Botón para editar-->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="{{ $puesto->id }}"
                                            data-nombre="{{ $puesto->nombre }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $puesto->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay puestos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $puestos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editPuestosForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Puesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" name="category_id" value="2">
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
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

<!-- Modal Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deletePuestosForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar puesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este puesto?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Modal de Editar
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID de la materia prima

            // Actualizar la acción del formulario con el ID de la materia prima
            $('#editPuestosForm').attr('action', `{{ url('puestos') }}/${id}`);

            // Prellenar los campos del formulario con los datos de la materia prima
            $('#edit_nombre').val(button.data('nombre'));
            $('#edit_observacion').val(button.data('observacion'));
            $('#edit_unidad_medida').val(button.data('unidad_medida'));
            $('#edit_unit_price').val(button.data('unit_price'));
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID de la materia prima

            // Actualizar la acción del formulario con el ID de la materia prima
            $('#deletePuestosForm').attr('action', `{{ url('puestos') }}/${id}`);
        });
    });
    
</script>
<script>
    $(document).ready(function() {
        $('#textoBusqueda').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('table tbody tr').each(function() {
                var nombre = $(this).find('td:nth-child(2)').text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>
@endsection