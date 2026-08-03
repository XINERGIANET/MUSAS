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
<h1>Usuarios</h1>
<p>Lista de usuarios</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Usuarios...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>PIN</th>
                                    <th>Sede</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="user-body">
                                @forelse ($usuarios as $usuario)
                                <tr>
                                    <td>{{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}</td>
                                    <td>{{ $usuario->nombre }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->rol->nombre }}</td>
                                    <td>{{ $usuario->pin ? : 'Sin PIN'}}</td>
                                    <td>{{ $usuario->headquarter ? $usuario->headquarter->nombre : 'Sin sede' }}</td>
                                    <td>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $usuario->id }}" data-nombre="{{ $usuario->nombre }}" data-email="{{ $usuario->email }}" data-rol_id="{{ $usuario->rol_id }}" data-activo="{{ $usuario->activo }}" data-pin="{{ $usuario->pin }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $usuario->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $usuarios->links('pagination::bootstrap-4') }}
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
            <form id="editUsuarioForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="text" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_password" class="form-label">Nueva Contraseña (opcional)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_pin" class="form-label">PIN (opcional)</label>
                        <input type="number" class="form-control" id="edit_pin" name="pin" min="0" max="9999" placeholder="4 dígitos">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_rol_id" class="form-label">Rol</label>
                        <select class="form-control" id="edit_rol_id" name="rol_id" required>
                            @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_activo" class="form-label">Estado</label>
                        <select class="form-control" id="edit_activo" name="activo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
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
            <form id="deleteUsuarioForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este usuario?</p>
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
            const id = button.data('id'); // Obtener el ID del usuario

            // Actualizar la acción del formulario con el ID del usuario
            $('#editUsuarioForm').attr('action', `{{ url('usuarios') }}/${id}`);

            // Prellenar los campos del formulario con los datos del usuario
            $('#edit_nombre').val(button.data('nombre'));
            $('#edit_email').val(button.data('email'));
            $('#edit_rol_id').val(button.data('rol_id'));
            $('#edit_activo').val(button.data('activo'));
            $('#edit_pin').val(button.data('pin'));
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID del usuario

            // Actualizar la acción del formulario con el ID del usuario
            $('#deleteUsuarioForm').attr('action', `{{ url('usuarios') }}/${id}`);
        });
    });

    $('#textoBusqueda').on('input', function() {
        let query = $(this).val();

        if (query === '') {
            window.location.href = "{{ route('usuarios.index') }}";
        }

        $.ajax({
            url: '{{ route("buscar-users.filtro") }}',
            method: 'GET',
            data: {
                query: query
            },
            success: function(response) {
                let rows = '';

                if (response.user.length > 0) {
                    response.user.forEach((u, index) => {
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${u.nombre}</td>
                                <td>${u.email}</td>
                                <td>${u.rol ? u.rol.nombre : 'Sin rol'}</td>
                                <td>${u.pin ? u.pin : 'Sin PIN'}</td>
                                <td>${u.headquarter ? u.headquarter.nombre : 'Sin sede'}</td>
                                <td>${u.activo ? 'Activo' : 'Inactivo'}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="${u.id}"
                                        data-nombre="${u.nombre}"
                                        data-email="${u.email}"
                                        data-rol_id="${u.rol_id}"
                                        data-activo="${u.activo}"
                                        data-pin="${u.pin || ''}">
                                        Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="${u.id}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('.d-flex.justify-content-center.mt-3').html('');
                } else {
                    rows = `<tr><td colspan="8" class="text-center">No se encontraron resultados.</td></tr>`;
                    $('.d-flex.justify-content-center.mt-3').html('');
                }

                $('#user-body').html(rows);
            }
        });
    });
</script>
@endsection
