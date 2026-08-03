@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('payment_methods.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('payment_methods.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Métodos de pago</h2>
<p>Lista de métodos de pago</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
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
                                @forelse ($paymentMethods as $paymentMethod)
                                <tr>
                                    <td>{{ ($paymentMethods->currentPage() - 1) * $paymentMethods->perPage() + $loop->iteration }}</td>
                                    <td>{{ $paymentMethod->nombre }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning btn-icon" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $paymentMethod->id }}" data-nombre="{{ $paymentMethod->nombre }}" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $paymentMethod->id }}" title="Eliminar">
                                             <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay sedes registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $paymentMethods->links('pagination::bootstrap-4') }}
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
            <form id="editHeadquarterForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
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
            <form id="deleteHeadquarterForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Metodo de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este metodo de pago?</p>
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
            const id = button.data('id'); // Obtener el ID de la sede
            const nombre = button.data('nombre'); // Obtener el nombre de la sede

            // Actualizar la acción del formulario con el ID de la sede
            $('#editHeadquarterForm').attr('action', `{{ url('payment_methods') }}/${id}`);

            // Prellenar los campos del formulario con los datos de la sede
            $('#edit_nombre').val(nombre);
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID de la sede

            // Actualizar la acción del formulario con el ID de la sede
            $('#deleteHeadquarterForm').attr('action', `{{ url('payment_methods') }}/${id}`);
        });
    });
</script>
@endsection