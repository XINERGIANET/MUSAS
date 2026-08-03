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
<h2>Clientes</h2>
<p>Lista de clientes</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row mb-3">
                        <!-- Filtro por rango de fechas a la izquierda -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text"
                                    class="form-control"
                                    id="textoBusqueda"
                                    placeholder="Buscar Nombre Cliente...">
                            </div>
                        </div>

                        <!-- Botones de importar y exportar a la derecha -->
                        <div class="col-md-6 offset-md-2 text-end">
                            <button class="btn btn-success" type="button" id="btnImportar">
                                <i class="fas fa-file-import"></i> Importar
                            </button>
                            <button class="btn btn-info" type="button" id="btnExportar">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>RUC / DNI</th>
                                    <th>Nombre Completo / Razón Social</th>
                                    <th>Teléfono</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 14px">
                                @forelse ($clients as $client)
                                <tr>
                                    <td>{{ ($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration }}</td>
                                    <td>{{ $client->ruc_dni }}</td>
                                    <td>{{ $client->nombre }}</td>
                                    <td>{{ $client->telefono }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning btn-icon" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $client->id }}" data-ruc_dni="{{ $client->ruc_dni }}" data-nombre="{{ $client->nombre }}" data-telefono="{{ $client->telefono }}" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $client->id }}" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay clientes registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $clients->links('pagination::bootstrap-4') }}
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
            <form id="editClientForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_ruc_dni" class="form-label">RUC / DNI</label>
                        <input type="text" class="form-control" id="edit_ruc_dni" name="ruc_dni" required  maxlength="11" onkeypress="isNumber(event)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre" class="form-label">Nombre Completo / Razón Social</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="edit_telefono" name="telefono" required maxlength="9" onkeypress="isNumber(event)">
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
            <form id="deleteClientForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este cliente?</p>
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
            const id = button.data('id'); // Obtener el ID del cliente

            // Actualizar la acción del formulario con el ID del cliente
            $('#editClientForm').attr('action', `{{ url('clients') }}/${id}`);

            // Prellenar los campos del formulario con los datos del cliente
            $('#edit_ruc_dni').val(button.data('ruc_dni'));
            $('#edit_nombre').val(button.data('nombre'));
            $('#edit_telefono').val(button.data('telefono'));
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID del cliente

            // Actualizar la acción del formulario con el ID del cliente
            $('#deleteClientForm').attr('action', `{{ url('clients') }}/${id}`);
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
@endsection
