@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('expenses.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('expenses.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Egresos</h2>
<p>Lista de egresos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <form action="" method="GET">
                        <div class="row align-items-center g-3">
                            <!-- Fecha inicial -->
                            <div class="col-md-2">
                                <label for="start_date" class="form-label small">Fecha Inicial</label>
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                    value="{{ request()->start_date ? request()->start_date : '' }}">
                            </div>
                            
                            <!-- Fecha final -->
                            <div class="col-md-2">
                                <label for="end_date" class="form-label small">Fecha Final</label>
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                    value="{{ request()->end_date ? request()->end_date : '' }}">
                            </div>
                            
                            <!-- Botones -->
                            <div class="col-md-3">
                                <label class="form-label small invisible">Acciones</label>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <button class="btn btn-danger" type="button" id="pdfBtn">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Espacio restante -->
                            <div class="col-md-2"></div>
                        </div>
                    </form>
                    <div class="table-responsive mt-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tipo Comprobante</th>
                                    <th>Número</th>
                                    <th>Proveedor</th>
                                    <th>Monto</th>
                                    <th>Medio de Pago</th>
                                    <th>Fecha</th>
                                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia'))
                                    <th>Sede</th>
                                    @endif
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            </thead>
                            <tbody>
                                @forelse ($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->tipo_comprobante }}</td>
                                    <td>{{ $expense->invoice_number }}</td>
                                    <td>{{ $expense->supplier ? $expense->supplier->razon_social : 'Sin Proveedor'}}</td>
                                    <td>S/ {{ number_format($expense->monto, 2) }}</td>
                                    <td>{{ $expense->paymentMethod->nombre }}</td>
                                    <td>{{ $expense->fecha }}</td>
                                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia'))
                                    <td>{{ $expense->sede ? $expense->sede->nombre : 'Sin Sede' }}</td>
                                    @endif

                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="{{ $expense->id }}"
                                            data-tipo_comprobante="{{ $expense->tipo_comprobante }}"
                                            data-invoice_number="{{ $expense->invoice_number }}"
                                            data-supplier_id="{{ $expense->supplier_id }}"
                                            data-supplier_name="{{ $expense->supplier ? $expense->supplier->razon_social : '' }}"
                                            data-monto="{{ $expense->monto}}"
                                            data-payment_method_id="{{ $expense->payment_method_id }}"
                                            data-detalle="{{ $expense->detalle }}"
                                        >
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Sin Registros</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expenses->links('pagination::bootstrap-4') }}
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
                        <label for="tipo_comprobante" class="form-label">Tipo de Comprobante</label>
                        <select class="form-select" id="tipo_comprobante" name="tipo_comprobante" required>
                            <option value="Factura">Factura</option>
                            <option value="Boleta">Boleta</option>
                            <option value="Recibo por honorario">Recibo por honorario</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="invoice_number" class="form-label">Número de Comprobante</label>
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Buscar Proveedor</label>
                        <div class="input-group">
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">Seleccionar..</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-xs d-none"><i class="ti ti-search"></i></button>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="detalle" class="form-label">Detalle</label>
                        <textarea class="form-control" id="detalle" name="detalle" rows="3" required></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_method_id" class="form-label">Medio de Pago</label>
                        <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                            @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia'))
                    <div class="col-md-6 mb-3">
                        <label for="sede_id" class="form-label">Seleccionar sede</label>
                        <select class="form-select" id="sede_id" name="sede_id" required>
                            @foreach($sede as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if (auth()->user()->hasRole('adminSede'))
                    <input type="hidden" name="sede_id" value="{{ auth()->user()->sede_id }}">
                    @endif

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
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const tipo_comprobante = button.data('tipo_comprobante');
            const invoice_number = button.data('invoice_number');
            const monto = button.data('monto');
            const payment_method_id = button.data('payment_method_id');
            const detalle = button.data('detalle');
            const modal = $(this);
            modal.find('#editClientForm').attr('action', `{{ url('expenses') }}/${id}`);

            modal.find('#tipo_comprobante').val(tipo_comprobante);
            modal.find('#invoice_number').val(invoice_number);
            modal.find('#monto').val(monto);
            modal.find('#payment_method_id').val(payment_method_id);
            modal.find('#detalle').val(detalle);

           const supplier_id = button.data('supplier_id');
            const supplier_name = button.data('supplier_name');
            if (supplier_id && supplier_name) {
                const newOption = new Option(supplier_name, supplier_id, true, true);
                $('#supplier_id').append(newOption).trigger('change');
            }
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID del cliente

            // Actualizar la acción del formulario con el ID del cliente
            $('#deleteClientForm').attr('action', `{{ url('clients') }}/${id}`);
        });
    });
</script>
@endsection
@section('scripts')
    <!-- jQuery y Select2 deben cargarse primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
       $('#supplier_id').select2({
            theme: 'bootstrap-5',
            minimumInputLength: 3,
            language: 'es',
            dropdownParent: $('#editModal'),
            ajax: {
                url: '{{ route('providers.api') }}'
            }
        });
    </script>
@endsection
