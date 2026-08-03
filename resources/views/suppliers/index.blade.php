@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('suppliers.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('suppliers.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Proveedores</h2>
<p>Lista de proveedores</p>
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
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Proveedor...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>RUC</th>
                                    <th>Razón Social</th>
                                    <th>Nombre Comercial</th>
                                    <th>Tipo</th>
                                    <th>Días para pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="supplier-body">
                                @forelse ($suppliers as $supplier)
                                <tr>
                                    <td>{{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}</td>
                                    <td>{{ $supplier->ruc }}</td>
                                    <td>{{ $supplier->razon_social }}</td>
                                    <td>{{ $supplier->nombre_comercial }}</td>
                                    <td>
                                        @if($supplier->tipo == 'C')
                                            Contado
                                        @else
                                            Crédito
                                        @endif
                                    </td>

                                    <td>{{ $supplier->dias_pago }}</td>
                                    <td>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning btn-icon" data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $supplier->id }}" data-ruc="{{ $supplier->ruc }}" data-razon_social="{{ $supplier->razon_social }}" data-nombre_comercial="{{ $supplier->nombre_comercial }}" data-tipo="{{ $supplier->tipo }}" data-dias_pago="{{ $supplier->dias_pago }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $supplier->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay proveedores registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $suppliers->links('pagination::bootstrap-4') }}
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
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_ruc" class="form-label">RUC</label>
                        <input type="text" class="form-control" id="edit_ruc" name="ruc" required maxlength="11" onkeypress="isNumber(event)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_razon_social" class="form-label">Razón Social</label>
                        <input type="text" class="form-control" id="edit_razon_social" name="razon_social" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre_comercial" class="form-label">Nombre Comercial</label>
                        <input type="text" class="form-control" id="edit_nombre_comercial" name="nombre_comercial" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_tipo" class="form-label">Tipo</label>
                        <select class="form-control" id="edit_tipo" name="tipo" required
                            onchange="toggleDiasPago(this.value)">
                            <option value="">Seleccione tipo</option>
                            <option value="C">Contado</option>
                            <option value="R">Crédito</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="diasPagoContainer">
                        <label for="edit_dias_pago" class="form-label">Días para pago</label>
                        <input type="number" class="form-control" id="edit_dias_pago" name="dias_pago" required min="0" required onkeypress="isNumber(event)">
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
            <form id="deleteSupplierForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este proveedor?</p>
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
            const id = button.data('id'); // Obtener el ID del proveedor

            // Actualizar la acción del formulario con el ID del proveedor
            $('#editSupplierForm').attr('action', `{{ url('suppliers') }}/${id}`);

            // Prellenar los campos del formulario con los datos del proveedor
            $('#edit_ruc').val(button.data('ruc'));
            $('#edit_razon_social').val(button.data('razon_social'));
            $('#edit_nombre_comercial').val(button.data('nombre_comercial'));
            $('#edit_tipo').val(button.data('tipo'));
            $('#edit_dias_pago').val(button.data('dias_pago'));
            toggleDiasPago(button.data('tipo'), button.data('dias_pago'));
        });

        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID del proveedor

            // Actualizar la acción del formulario con el ID del proveedor
            $('#deleteSupplierForm').attr('action', `{{ url('suppliers') }}/${id}`);
        });
    });

    $('#textoBusqueda').on('input', function() {
        let query = $(this).val();

        if (query === '') {
            window.location.href = "{{ route('suppliers.index') }}";
        }

        $.ajax({
            url: '{{ route("buscar-suppliers.filtro") }}',
            method: 'GET',
            data: {
                query: query
            },
            success: function(response) {
                let rows = '';

                if (response.supplier.length > 0) {
                    response.supplier.forEach((s, index) => {
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${s.ruc}</td>
                                <td>${s.razon_social}</td>
                                <td>${s.nombre_comercial}</td>
                                <td>${s.tipo}</td>
                                <td>${s.dias_pago}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="${s.id}"
                                        data-ruc="${s.ruc}"
                                        data-razon_social="${s.razon_social}"
                                        data-nombre_comercial="${s.nombre_comercial}"
                                        data-tipo="${s.tipo}"
                                        data-dias_pago="${s.dias_pago}">
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
                    rows = `<tr><td colspan="7" class="text-center">No se encontraron resultados.</td></tr>`;
                    $('.d-flex.justify-content-center.mt-3').html('');
                }

                $('#supplier-body').html(rows);
            }
        });
    });

    function toggleDiasPago(value, diasPago) {
        const diasPagoContainer = document.getElementById('diasPagoContainer');
        const diasPagoInput = document.getElementById('edit_dias_pago');

        if (value === 'C') {
            diasPagoContainer.classList.add('d-none');
            diasPagoInput.value = '0';
        } else {
            diasPagoContainer.classList.remove('d-none');
            diasPagoInput.value = diasPago || '';
        }
    }


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
