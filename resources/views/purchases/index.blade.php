@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    @if($tipo === 'egresoSede')
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('purchases.create', ['tipo' => 'egresoSede']) }}">Histórico</a>
    </li>
    @else
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('purchases.create', ['tipo' => $tipo]) }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('purchases.index', ['tipo' => $tipo]) }}">Histórico</a>
    </li>
    @endif
</ul>
@endsection

@section('header')
<h2>{{ $title }}</h2>
<p>{{ $subtitle }}</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Historial de Facturas -->
                        <div class="row">
                            <!-- Fila de filtros y botones en la misma línea -->
                            <div class="col-12">
                                <form action="" method="GET">
                                    <div class="row align-items-end g-3">
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
                                        <!-- Proveedor -->
                                        <div class="col-md-3">
                                            <label for="supplier_id" class="form-label small">
                                                @if($tipo === 'egresoSede')
                                                    Sede
                                                @else
                                                    Proveedor
                                                @endif
                                            </label>

                                            @if($tipo === 'egresoSede')
                                                <!-- Filtro por Sede -->
                                            <select class="form-control" id="sede_id" name="sede_id">
                                                <option value="" {{ request()->sede_id == '' ? 'selected' : '' }}>Seleccionar sede</option>
                                                @foreach ($headquarters as $headquarter)
                                                    <option value="{{ $headquarter->id }}" 
                                                            {{ request()->sede_id == $headquarter->id ? 'selected' : '' }}>
                                                        {{ $headquarter->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @else
                                                <!-- Filtro por Proveedor -->
                                                <select class="form-control" id="supplier_id" name="supplier_id">
                                                    <option value="" {{ request()->supplier_id == '' ? 'selected' : '' }}>Seleccionar proveedor</option>
                                                    @foreach ($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}"
                                                                {{ request()->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                            {{ $supplier->razon_social }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>

                                        <div class="col-md-3">
                                            <label for="search-product" class="form-label">Filtrar por Producto</label>
                                            <input hidden type="number" id="product_id" name="product_id" placeholder="">
                                            <input type="text" class="form-control" id="search-product" placeholder="Todos los productos">
                                        </div>

                                        @if($tipo === 'egresoSede')
                                        <div class="col-md-3">
                                            <label for="search-usuario" class="form-label">Filtrar por Usuario</label>
                                            <select class="form-control" id="user_id" name="user_id">
                                                <option value="" {{ request()->user_id == '' ? 'selected' : '' }}>Seleccionar usuario</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}" 
                                                            {{ request()->user_id == $user->id ? 'selected' : '' }}>
                                                        {{ $user->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif

                                        <input hidden type="text" name="tipo" value="{{ $tipo }}">

                                        <!-- Botones -->
                                        <div class="col-md-12">
                                            <input type="hidden" name="tipo" id="tipo" value="{{ $tipo }}">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-filter"></i> Filtrar
                                                </button>
                                                <div class="w-50s me-2">
                                                    <a href="{{ route('purchases.index', ['tipo' => $tipo]) }}" 
                                                    class="btn btn-warning w-100" id="btnLimpiar">
                                                    Limpiar
                                                    </a>
                                                </div>

                                                @if($tipo !== 'egresoSede')
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                        <i class="fas fa-file-pdf"></i> INFORMES PDF
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                        <a class="dropdown-item btn-pdf" href="#">
                                                            <i class="bi bi-file-earmark-text"></i> DETALLE POR PROOVEDOR
                                                        </a>
                                                        </li>
                                                        <li>
                                                        <a class="dropdown-item btn-pdf-general" href="#">
                                                            <i class="bi bi-file-earmark-text"></i> DETALLE TOTAL POR PROOVEDOR
                                                        </a>
                                                        </li>
                                                        <li>
                                                        <a class="dropdown-item btn-producto" href="#">
                                                            <i class="bi bi-file-earmark-text"></i> DETALLE POR PRODUCTO
                                                        </a>
                                                        </li>
                                                        <li>
                                                        <a class="dropdown-item btn-producto-todo" href="#">
                                                            <i class="bi bi-file-earmark-text"></i> DETALLE TOTAL POR PRODUCTO
                                                        </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <button class="btn btn-success" type="button" id="excelBtn">
                                                    <i class="fas fa-file-excel"></i> Excel
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Fila del total - abajo a la derecha -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end">
                                    <div>
                                        <h4>
                                            <strong>TOTAL: S/ {{ number_format($total, 2, '.', ',') }}</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h4 class="mt-3">Historial de Facturas</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="invoiceHistoryTable">
                                <thead>
                                    <tr>
                                        <th>N° Comprobante</th>
                                        @if($tipo === 'egresoSede')
                                            <th>Usuario</th>  
                                            <th>Sede</th>  
                                        @else
                                            <th>Proveedor</th>  
                                        @endif
                                        <th>Fecha</th>
                                        <th>Método de Pago</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($purchases->count())
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->invoice_number ?? '---' }}</td>
                                                @if($tipo === 'egresoSede')
                                                    <td>{{ $purchase->user->nombre ?? 'Sin usuario' }}</td>  
                                                    <td>{{ $purchase->sede ? $purchase->sede->nombre : 'Sin sede' }}</td> 
                                                @else
                                                    <td>{{ $purchase->supplier->razon_social ?? 'Sin proveedor' }}</td>
                                                @endif
                                                <td>{{ \Carbon\Carbon::parse($purchase->date)->format('Y-m-d') }}</td>
                                                <td>{{ $purchase->paymentMethod->nombre ?? 'Sin método de pago' }}</td>
                                                <td>{{ number_format($purchase->details->sum('subtotal'), 2, '.', '') }}</td>
                                                <td>{{ $purchase->estado == 0 ? 'Activo' : 'Anulado' }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm btn-icon btn-show"
                                                            data-id="{{ $purchase->id }}"
                                                            data-tipo="{{ $tipo }}"
                                                            title="Ver Detalle">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>

                                                    @if($purchase->estado == 0)
                                                        <button class="btn btn-warning btn-sm btn-icon btn-edit"
                                                                data-id="{{ $purchase->id }}"
                                                                data-tipo="{{ $tipo }}"
                                                                title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>

                                                        <button class="btn btn-danger btn-sm btn-icon btn-eliminar"
                                                                data-id="{{ $purchase->id }}"
                                                                data-tipo="{{ $tipo }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#eliminarModal"
                                                                title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">Sin Registros</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $purchases->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de la Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto / Insum</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Aquí puedes incluir el formulario de edición -->
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <!-- Fila 1: N° Comprobante y Proveedor -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editInvoiceNumber" class="form-label">N° Comprobante</label>
                            <input type="text" class="form-control" id="editInvoiceNumber" name="invoice_number">
                        </div>
                        <div class="col-md-6">
                            <label for="editSupplier" class="form-label">Proveedor</label>
                            <select class="form-control" id="editSupplier" name="supplier_id">
                                <option value="">Seleccionar proveedor</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Fila 2: Fecha, Método de Pago y Total -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="editDate" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="editDate" name="date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="editPaymentMethod" class="form-label">Método de Pago</label>
                            <select class="form-control" id="editPaymentMethod" name="payment_method_id" required>
                                <option value="">Seleccionar método de pago</option>
                                @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="editTotal" class="form-label">Total</label>
                            <input type="number" class="form-control" id="editTotal" name="total" step="0.01" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este registro?</p>
            </div>
            <div class="modal-footer">
                <form id="formEliminar" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1000;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
    }
</style>

<!-- Script para manejar la solicitud AJAX -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
var products = @json($products);
$('#search-product').autocomplete({
    source: function(request, response) {
        var results = [];
        if (products && products.length) {
            for (var i = 0; i < products.length; i++) {
                var product = products[i];
                if (product && product.nombre && 
                    product.nombre.toLowerCase().indexOf(request.term.toLowerCase()) !== -1) {
                    results.push({
                        label: product.nombre,
                        value: product.nombre,
                        id: product.id
                    });
                }
            }
        }
        response(results.slice(0, 15));
    },
    select: function(event, ui) {
        if (ui.item && ui.item.id) {
            $('#product_id').val(ui.item.id);
        }
    },
    change: function(event, ui) {
        if (!ui.item) {
            $('#product_id').val('');
        }
    }
});

// Limpiar cuando se borra el texto
$('#search-product').on('input', function() {
    if ($(this).val() === '') {
        $('#product_id').val('');
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const spinner = document.getElementById('global-spinner');
        const editForm = document.getElementById('editForm');

        // Ocultar el spinner inicialmente
        spinner.classList.remove('spinner-visible');
        spinner.classList.add('spinner-hidden');

        // Mostrar el spinner al enviar el formulario
        editForm.addEventListener('submit', function() {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const eliminarModal = document.getElementById('eliminarModal');
        eliminarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const tipo = button.getAttribute('data-tipo');

            // Ruta al controlador que actualiza el estado
            const form = document.getElementById('formEliminar');
            form.action = '{{ url("") }}/' + tipo + '/' + id + '/estado';
        });
    });

    $(document).ready(function() {
        // Cuando se hace clic en el botón "Ver Detalle"
        $('.btn-show').click(function() {
            var id = $(this).data('id'); // Obtener el ID de la compra
            var tipo = $(this).data('tipo');

            // Limpiar la tabla de detalles
            $('#tbl-items').html('');

            // Hacer una solicitud AJAX para obtener los detalles
            $.ajax({
                url: '{{ route("purchases.show", "") }}/' + id + '?tipo=' + tipo, // Ruta corregida
                method: 'GET',
                success: function(data) {
                    var html = '';

                    // Construir las filas de la tabla con los detalles
                    data.details.forEach(function(detail) {
                        html += `
                            <tr>
                                <td>${detail.product ? detail.product.nombre : (detail.insumo ? detail.insumo.nombre : 'N/A')}</td>
                                <td>${detail.quantity}</td>
                                <td>${detail.unit_price}</td>
                                <td>${detail.subtotal}</td>
                            </tr>
                        `;
                    });

                    // Insertar las filas en la tabla
                    $('#tbl-items').html(html);

                    // Mostrar el modal
                    $('#showModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });
    });

    // Manejar el clic en el botón "Editar"
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var tipo = $(this).data('tipo');

        $('#editForm').data('id', id);
        $('#editForm').data('tipo', tipo);

        // Construimos la URL dinámicamente usando la ruta Laravel
        var url = '{{ route("registro.edit", ["tipo" => "TIPO", "id" => "ID"]) }}'
            .replace('TIPO', tipo)
            .replace('ID', id);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                const registro = data.registro;

                $('#editInvoiceNumber').val(registro.invoice_number);
                $('#editSupplier').val(registro.supplier_id);
                $('#editDate').val(registro.date);
                $('#editPaymentMethod').val(registro.payment_method_id);

                let total = 0;
                if (registro.details && Array.isArray(registro.details)) {
                    total = registro.details.reduce((sum, detail) => {
                        let subtotal = parseFloat(detail.subtotal) || 0;
                        return sum + subtotal;
                    }, 0);
                }
                $('#editTotal').val(total.toFixed(2));
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                ToastError.fire({
                    text: 'No se pudo cargar los datos'
                });
            }
        });

        $('#editModal').modal('show');
    });

    // Manejar el envío del formulario de edición
    $('#editForm').submit(function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        var tipo = $(this).data('tipo');

        var url = '{{ route("registro.update", ["tipo" => "TIPO", "id" => "ID"]) }}';
        url = url.replace("TIPO", tipo).replace("ID", id);

        var token = $('input[name="_token"]').val();

        var formData = {
            invoice_number: $('#editInvoiceNumber').val(),
            supplier_id: $('#editSupplier').val(),
            date: $('#editDate').val(),
            payment_method_id: $('#editPaymentMethod').val(),
            _token: token,
            _method: 'PUT'
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#editModal').modal('hide');
                ToastMessage.fire({
                    icon: 'success',
                    text: 'Registro actualizado correctamente.'
                });
                location.reload();
            },
            error: function(xhr) {
                ToastError.fire({
                    text: 'No se pudo actualizar el registro'
                });
            }
        });
    });

    $(document).on('click', '.btn-pdf', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;
        const tipo = document.getElementById('tipo').value;

        // Usar la nueva ruta
        let pdfUrl = '{{ route("purchases.pdf") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        if (tipo) params.append('tipo', tipo);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        console.log('URL generada:', pdfUrl);

        // Crear un enlace temporal para forzar la descarga
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_' + tipo + '.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $(document).on('click', '.btn-pdf-general', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;
        const tipo = document.getElementById('tipo').value;

        // Usar la nueva ruta
        let pdfUrl = '{{ route("purchases.pdfGeneral") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        if (tipo) params.append('tipo', tipo);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        console.log('URL generada:', pdfUrl);

        // Crear un enlace temporal para forzar la descarga
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_' + tipo +'_general' + '.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Alternativa: abrir en nueva ventana
        // window.open(pdfUrl, '_blank');
    });

    $(document).on('click', '.btn-producto', function() {
                const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;
        const productId = document.getElementById('product_id').value;
        const tipo = document.getElementById('tipo').value;
        // Validar que hay un producto seleccionado
        if (!productId) {
            ToastError.fire({
                text: 'Seleccione un Producto a filtrar'
            });
            return;
        }

        let pdfUrl = '{{ route("purchases.pdfProduct") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        if (tipo) params.append('tipo', tipo);
        params.append('product_id', productId);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        const productName = document.getElementById('search-product').value || 'producto';
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = `reporte_${productName.toLowerCase().replace(/\s+/g, '_')}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $(document).on('click', '.btn-producto-todo', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;
        const tipo = document.getElementById('tipo').value;

        let pdfUrl = '{{ route("purchases.pdfAllProducts") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        if (tipo) params.append('tipo', tipo);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_todos_los_productos_'+ tipo +'.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // NUEVO: Botón PDF Todos los Productos (agrupados por producto)
    document.getElementById('excelBtn').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        let excelUrl = '{{ route("purchases.excel") }}';
        const params = new URLSearchParams();

        params.append('tipo', '{{ $tipo }}'); 
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        if (params.toString()) {
            excelUrl += '?' + params.toString();
        }

        const link = document.createElement('a');
        link.href = excelUrl;
        link.download = 'reporte_compras.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>
@endsection