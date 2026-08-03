@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('staff.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('staff.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Personal</h2>
<p>Lista de Personal</p>
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
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Nombre...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped text-xs">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>DNI</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Teléfono</th>
                                    <th>Puesto</th>
                                    <th>Sede</th>
                                    <th>Fecha de Nacimiento</th>
                                    <th>Sueldo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="staff-body" style="font-size: 14px">
                                @forelse ($staff as $s)
                                <tr>
                                    <td>{{ ($staff->currentPage() - 1) * $staff->perPage() + $loop->iteration }}</td>
                                    <td>{{ $s->dni ?? '-' }}</td>
                                    <td>{{ $s->nombre }}</td>
                                    <td>{{ $s->telefono ?? '-' }}</td>
                                    <td>{{ $s->puesto->nombre }}</td>
                                    <td>{{ $s->headquarter->nombre ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($s->fecha_nacimiento)->format('d/m/Y') }}</td>
                                    <td>S/ {{ number_format($s->sueldo, 2) }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="{{ $s->id }}"
                                            data-dni="{{ $s->dni }}"
                                            data-nombre="{{ $s->nombre }}"
                                            data-telefono="{{ $s->telefono }}"
                                            data-puesto="{{ $s->puesto }}"
                                            data-headquarter_id="{{ $s->headquarter_id }}"
                                            data-fecha_nacimiento="{{ \Carbon\Carbon::parse($s->fecha_nacimiento)->format('Y-m-d') }}"
                                            data-sueldo="{{ $s->sueldo }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $s->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay personal registrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $staff->links('pagination::bootstrap-4') }}
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
            <form id="editStaffForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Personal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_dni" class="form-label">DNI</label>
                        <input type="text" class="form-control" id="edit_dni" name="dni" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre" class="form-label">Nombres y Apellidos</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="edit_telefono" name="telefono" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_puesto" class="form-label">Puesto</label>
                        <select class="form-control" id="edit_puesto" name="puesto" required>
                            <option value="">Seleccione un puesto</option>
                            @foreach ($puestos as $puesto)
                            <option value="{{ $puesto->nombre }}">{{ $puesto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_headquarter_id" class="form-label">Sede</label>
                        <select class="form-control" id="edit_headquarter_id" name="headquarter_id" required>
                            <option value="">Seleccione una sede</option>
                            @foreach ($headquarters as $headquarter)
                            <option value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_sueldo" class="form-label">Sueldo</label>
                        <input type="number" class="form-control" id="edit_sueldo" name="sueldo" step="0.01" min="0" required>
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
            <form id="deleteStaffForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Personal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este personal?</p>
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
            const button = $(event.relatedTarget);
            const id = button.data('id');

            $('#editStaffForm').attr('action', `{{ url('staff') }}/${id}`);

            $('#edit_dni').val(button.data('dni'));
            $('#edit_nombre').val(button.data('nombre'));
            $('#edit_telefono').val(button.data('telefono'));
            $('#edit_puesto').val(button.data('puesto'));
            $('#edit_headquarter_id').val(button.data('headquarter_id'));
            $('#edit_fecha_nacimiento').val(button.data('fecha_nacimiento'));
            $('#edit_sueldo').val(button.data('sueldo'));
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            $('#deleteStaffForm').attr('action', `{{ url('staff') }}/${id}`);
        });

        $('#textoBusqueda').on('input', function() {
            let query = $(this).val();

            if (query === '') {
                window.location.href = "{{ route('staff.index') }}";
            }

            $.ajax({
                url: '{{ route("buscar-staff.filtro") }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    let rows = '';

                    if (response.staff.length > 0) {
                        response.staff.forEach((s, index) => {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${s.dni}</td>
                                    <td>${s.nombre}</td>
                                    <td>${s.telefono}</td>
                                    <td>${s.puesto}</td>
                                    <td>${s.headquarter?.nombre ?? 'Sin sede'}</td>
                                    <td>${s.fecha_nacimiento}</td>
                                    <td>S/ ${parseFloat(s.sueldo).toFixed(2)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="${s.id}"
                                            data-dni="${s.dni}"
                                            data-nombre="${s.nombre}"
                                            data-telefono="${s.telefono}"
                                            data-puesto="${s.puesto}"
                                            data-headquarter_id="${s.headquarter_id}"
                                            data-fecha_nacimiento="${s.fecha_nacimiento}"
                                            data-sueldo="${s.sueldo}">
                                            Editar
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="${s.id}">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('.d-flex.justify-content-center.mt-3').html('');
                    } else {
                        rows = `<tr><td colspan="6" class="text-center">No se encontraron resultados.</td></tr>`;
                        $('.d-flex.justify-content-center.mt-3').html('');
                    }

                    $('#staff-body').html(rows);
                }
            });
        });
    });
</script>
@endsection
