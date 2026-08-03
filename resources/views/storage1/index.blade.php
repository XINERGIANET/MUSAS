@extends('template.index')

@section('header')
<h1>Almacenamiento de Materia Prima</h1>
<p>Lista de Registros de Almacenamiento de Materia Prima</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <div class="row mb-6">
                            <!-- Filtro de búsqueda a la izquierda -->
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Nombre Materia Prima...">
                                </div>
                            </div>
                            <div class="col-md-8 d-flex justify-content align-items-center">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="bi bi-plus-circle"></i> Nueva Materia Prima
                                </button>
                                <a href="{{ route('storage.pdf',
                                [
                                'categoria' => 'prima'
                                ]) }}"
                                    class="btn btn-info mx-3" type="button" id="btnPDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                                @if(auth()->user()->rol_id == 1)
                                <a href="{{ route('stock.stockInicial', ['categoryId' => 1]) }}" class="btn btn-warning">
                                    <i class="bi bi-plus-circle"></i> Stock Inicial
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="row d-flex">
                            <div class="d-flex justify-content-end align-items-center">
                                <h5>
                                    <strong>TOTAL S/ {{ number_format($total, 2, '.', ',') }}</strong>
                                </h5>
                            </div>
                        </div>

                        <!-- Lista de Registros de Almacenamiento -->
                        <h4 class="mt-3 mb-2">Registros de Almacenamiento</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="storageTable">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($storages as $storage)
                                    <tr>
                                        <td>{{ $storage->product->nombre ?? 'Sin producto' }}</td>
                                        <td>{{ $storage->quantity }}</td>
                                        <td>S/ {{ number_format($storage->product->unit_price,2,  '.', ',') }}</td>
                                        <td>S/ {{ number_format($storage->quantity*$storage->product->unit_price,2,  '.', ',') }}</td>
                                        <td>
                                            @if($storage->id && $storage->quantity > 0)
                                            {{-- Producto real con stock - EDITAR --}}
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $storage->id }}"
                                                data-product-id="{{ $storage->product->id }}"
                                                data-quantity="{{ $storage->quantity }}"
                                                data-unit_price="{{ $storage->product->unit_price }}"
                                                title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @else
                                            {{-- Producto virtual - AGREGAR --}}
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $storage->id }}"
                                                data-product-id="{{ $storage->product->id }}"
                                                data-quantity="0"
                                                data-unit_price="{{ $storage->product->unit_price ?? 0 }}"
                                                title="Agregar al almacén">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endif
                                            @if($storage->id)
                                            <form action="{{ route('storage1.destroy', $storage->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger btn-delete-storage" data-id="{{ $storage->id }}" type="submit" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $storages->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="editClientForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Salida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" id="product_id" name="product_id">
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Cantidad actual</label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                            onkeypress="isDecimal(event)" min="0.01" step="0.01" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="quantity_out" class="form-label">Cantidad nueva</label>
                        <input type="number" class="form-control" id="quantity_out" name="quantity_out"
                            required onkeypress="isDecimal(event)" min="0" step="0.01">
                    </div>
                </div>

                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="unit_price" class="form-label">Precio actual (S/)</label>
                        <input type="text" class="form-control" id="unit_price" name="unit_price" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unit_price_out" class="form-label">Precio nuevo (S/)</label>
                        <input type="number" class="form-control" id="unit_price_out" name="unit_price_out"
                            onkeypress="isDecimal(event)" min="0.01" step="0.01">
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

<!-- Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Nueva Materia Prima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    @csrf
                    <input type="hidden" name="category_id" value="1">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <select class="form-control border-dark" id="unidad_medida" name="unidad_medida" required>
                            <option value="">Seleccione una unidad de medida</option>
                            @foreach ($unidadMedidas as $unidadMedida)
                            <option value="{{ $unidadMedida->nombre }}">{{ $unidadMedida->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1060;">
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

    .numeric-keypad {
        max-width: 300px;
        margin: 0 auto;
    }

    .num-btn {
        padding: 10px 0;
    }
</style>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const productId = button.data('product-id');
            const quantity = parseFloat(button.data('quantity')) || 0;
            const unit_price = parseFloat(button.data('unit_price')) || 0;

            const modal = $(this);
            const form = modal.find('#editClientForm');

            // Determinar si es virtual o real
            const isVirtual = !id || id === 'null' || id === null || id === 0 || id === '0';

            // Cambiar título del modal
            if (isVirtual) {
                modal.find('.modal-title').text('Agregar al Almacén');
                modal.find('button[type="submit"]').html('<i class="fas fa-plus"></i> Agregar');
            } else {
                modal.find('.modal-title').text('Editar Almacén');
                modal.find('button[type="submit"]').html('<i class="fas fa-save"></i> Guardar Cambios');
            }

            // Configurar la URL del formulario
            if (isVirtual) {
                form.attr('action', `{{ url('storage1') }}/0`);
            } else {
                form.attr('action', `{{ url('storage1') }}/${id}`);
            }

            // Limpiar métodos anteriores
            form.find('input[name="_method"]').remove();
            form.append('<input type="hidden" name="_method" value="PUT">');

            // Llenar campos del modal
            modal.find('#product_id').val(productId);
            modal.find('#quantity').val(quantity);
            modal.find('#unit_price').val(unit_price.toFixed(2));
            modal.find('#quantity_out').val(quantity);
            modal.find('#unit_price_out').val(unit_price.toFixed(2));
        });
    });

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

    function saveProduct() {
        const productName = document.getElementById('nombre').value.trim();
        const categoryId = document.querySelector('input[name="category_id"]').value;
        const unidadMedida = document.getElementById('unidad_medida').value;
        const saveButton = document.querySelector('[onclick="saveProduct()"]');

        // Validación básica
        if (!productName) {
            ToastError.fire({
                text: 'El nombre del producto es requerido'
            });
            return;
        }

        // Deshabilitar botón mientras se procesa
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        const csrfToken = document.querySelector('input[name="_token"]').value;

        // Preparar datos
        const formData = new FormData();
        formData.append('nombre', productName);
        formData.append('category_id', categoryId);
        formData.append('unidad_medida', unidadMedida);
        formData.append('_token', csrfToken);

        // Realizar petición AJAX
        fetch('{{ route("storage1.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Éxito
                    ToastMessage.fire({
                        icon: 'success',
                        text: data.message || 'Materia prima creada exitosamente'
                    }).then(() => {
                        // Limpiar formulario
                        document.getElementById('nombre').value = '';

                        // Cerrar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
                        modal.hide();

                        // Opcional: recargar la página o tabla
                        window.location.reload(); // Descomenta si necesitas recargar
                    });

                } else {
                    // Error del servidor
                    ToastError.fire({
                        text: data.message || 'Error al crear la materia prima'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ToastError.fire({
                    text: 'Error de conexión. Intenta nuevamente.'
                });
            })
            .finally(() => {
                // Rehabilitar botón
                saveButton.disabled = false;
                saveButton.innerHTML = '<i class="fas fa-save"></i> Guardar';
            });
    }
</script>
<script>
    $(document).ready(function() {
        $('#textoBusqueda').on('input', function() {
            const valor = $(this).val().trim();

            if (valor.length === 0) {
                return;
            }

            $.ajax({
                url: '{{ route("storage1.search") }}',
                type: 'GET',
                data: {
                    query: valor
                },
                success: function(data) {
                    const tbody = $('#storageTable tbody');
                    tbody.empty();

                    let total = 0;

                    if (data.length === 0) {
                        tbody.append('<tr><td colspan="5">No se encontraron resultados.</td></tr>');
                        $('strong:contains("TOTAL")').text('TOTAL S/ 0.00');
                        return;
                    }

                    const formatoMoneda = new Intl.NumberFormat('es-PE', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    data.forEach(item => {
                        total += item.subtotal;

                        let actionButtons = `
                            <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="${item.id}"
                                data-product-id="${item.product_id}"
                                data-quantity="${item.quantity}"
                                data-unit_price="${item.unit_price}"
                                title="${item.id && item.quantity > 0 ? 'Editar' : 'Agregar al almacén'}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        `;

                        if (item.id && item.id !== 0) {
                            actionButtons += `
                                <form action="{{ url('storage1') }}/${item.id}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-delete-storage" data-id="${item.id}" type="submit" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            `;
                        }

                        tbody.append(`
                            <tr>
                                <td>${item.nombre}</td>
                                <td>${item.quantity}</td>
                                <td>S/ ${formatoMoneda.format(item.unit_price)}</td>
                                <td>S/ ${formatoMoneda.format(item.subtotal)}</td>
                                <td>
                                    ${actionButtons}
                                </td>
                            </tr>
                        `);
                    });

                    const totalFormateado = new Intl.NumberFormat('es-PE', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(total);

                    $('strong:contains("TOTAL")').text('TOTAL S/ ' + totalFormateado);

                    // Agregar event listener para los botones de eliminar creados dinámicamente
                    $('.btn-delete-storage').off('click').on('click', function(e) {
                        e.preventDefault();
                        const form = $(this).closest('form');
                        ToastConfirm.fire({
                            text: '¿Desea eliminar este registro de almacén?',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error en búsqueda:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);

                    const tbody = $('#storageTable tbody');
                    tbody.empty();
                    tbody.append('<tr><td colspan="5">Error al realizar la búsqueda. Revise la consola para más detalles.</td></tr>');
                }
            });
        });
    });


    $('.btn-delete-storage').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        ToastConfirm.fire({
            text: '¿Desea eliminar este registro de almacén?',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#editClientForm').on('submit', function() {
            $('#global-spinner').removeClass('spinner-hidden').addClass('spinner-visible');
        });
    });
</script>