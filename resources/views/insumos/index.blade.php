@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('insumos.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('insumos.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Insumos</h2>
<p>Lista de Insumos</p>
@endsection

@section('styles')
<style>
    #btnExportar {
        background-color: #1D6F42 !important;
        border-color: #1D6F42 !important;
    }

    #btnPDF {
        background-color: #ED2224 !important;
        border-color: #ED2224 !important;
    }

    .provider-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        margin-bottom: 5px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .provider-item .provider-name {
        flex-grow: 1;
        margin-right: 10px;
    }

    .provider-item .btn-remove {
        padding: 2px 6px;
        font-size: 12px;
    }

    .add-provider-section {
        border-top: 1px solid #dee2e6;
        padding-top: 15px;
        margin-top: 15px;
    }
</style>
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
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Nombre Materia Prima...">
                            </div>
                        </div>
                        <div class="col-md-6 offset-md-2 text-end">
                            <a href="{{ route('products.pdf', ['category_id' => 1]) }}" class="btn btn-info" type="button" id="btnPDF">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Unidad de Medida</th>
                                    <th>Costo Unitario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productos-body">
                                @forelse ($insumo as $product)
                                <tr>
                                    <td>{{ ($insumo->currentPage() - 1) * $insumo->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->nombre }}</td>
                                    <td>{{ $product->unidad_medida }}</td>
                                    <td>{{ $product->unit_price }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-show btn-icon" data-id="{{ $product->id }}" title="Ver Detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning btn-edit btn-icon" data-id="{{ $product->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $product->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay Insumos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $insumo->links('pagination::bootstrap-4') }}
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
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" method="POST">   
                    @csrf <!-- Agregar CSRF token aquí -->
                    @method('PUT')
                    <input type="hidden" id="product_id" name="product_id">
                    <input type="hidden" name="category_id" value="4">

                    <!-- Primera fila: Nombre -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="product_name" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                            <div class="invalid-feedback">
                                Por favor ingrese el nombre del producto
                            </div>
                        </div>
                    </div>

                    <!-- Segunda fila: Precio y Unidad de Medida -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="unit_price" class="form-label">Precio Unitario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                                <div class="invalid-feedback">
                                    Ingrese un precio válido
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="unit_measure" class="form-label">Unidad de Medida</label>
                            <select class="form-select" id="unit_measure" name="unit_measure" required>
                                <option value="">Seleccione una unidad</option>
                                @foreach ($unidadMedidas as $unidadMedida)
                                <option value="{{ $unidadMedida->nombre }}">{{ $unidadMedida->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Seleccione una unidad de medida
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Proveedores -->
                    <div class="mb-3">
                        <label class="form-label">Proveedores Asociados</label>
                        <div id="product_provider_list" class="mb-3">
                            <!-- Aquí se mostrarán los proveedores existentes -->
                        </div>

                        <!-- Sección para agregar nuevo proveedor -->
                        <div class="add-provider-section">
                            <h6>Agregar Nuevo Proveedor</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-select" id="new_provider_select">
                                        <option value="">Seleccione un proveedor</option>
                                        <!-- Los proveedores se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-success" id="btn-add-provider">
                                        <i class="fas fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de submit -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteRawMaterialForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Insumo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este Insumo?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proveedores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentProviders = []; // Array para mantener los proveedores actuales
        let allProviders = []; // Array para todos los proveedores disponibles
        let providersLoaded = false; // Flag para saber si los proveedores ya se cargaron

        // Función para cargar todos los proveedores disponibles
        function loadAllProviders() {
            console.log('=== INICIANDO CARGA DE PROVEEDORES ===');
            console.log('URL de la ruta:', '{{ route("suppliersall") }}');
            
            // Si ya se cargaron, no volver a cargar
            if (providersLoaded && allProviders.length > 0) {
                console.log('Proveedores ya cargados, usando cache');
                updateProviderSelect();
                return Promise.resolve(allProviders);
            }

            return $.ajax({
                url: '{{ route("suppliersall") }}',
                method: 'GET',
                beforeSend: function() {
                    console.log('Enviando petición AJAX...');
                },
                success: function(data) {
                    
                    if (data) {
                        console.log('Propiedades de la respuesta:', Object.keys(data));
                        if (data.suppliers) {
                            console.log('data.suppliers:', data.suppliers);
                            console.log('Es array data.suppliers?', Array.isArray(data.suppliers));
                        }
                        if (data.data) {
                            console.log('data.data:', data.data);
                            console.log('Es array data.data?', Array.isArray(data.data));
                        }
                    }
                    
                    // Procesar la respuesta
                    if (Array.isArray(data)) {
                        allProviders = data;
                        console.log('✓ Usando respuesta directa como array');
                    } else if (data && Array.isArray(data.results)) {
                        allProviders = data.results;
                        console.log('✓ Usando data.results');
                    } else if (data && Array.isArray(data.suppliers)) {
                        allProviders = data.suppliers;
                        console.log('✓ Usando data.suppliers');
                    } else if (data && Array.isArray(data.data)) {
                        allProviders = data.data;
                        console.log('✓ Usando data.data');
                    } else {
                        console.error('❌ La respuesta no contiene un array válido');
                        allProviders = [];
                    }

                    
                    if (allProviders.length > 0) {
                        console.log('✓ Proveedores cargados correctamente');
                    } else {
                        console.warn('⚠️ No se encontraron proveedores disponibles');
                    }
                    
                    providersLoaded = true;
                    updateProviderSelect();
                },
                error: function(xhr, status, error) {
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    
                    // Intentar parsear el error
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        console.error('Error parseado:', errorData);
                    } catch (e) {
                        console.error('No se pudo parsear el error como JSON');
                    }
                    
                    allProviders = [];
                    providersLoaded = false;
                }
            });
        }

        // Función para actualizar el select de proveedores
        function updateProviderSelect() {
            const select = $('#new_provider_select');
            
            select.html('<option value="">Seleccione un proveedor</option>');

            if (!Array.isArray(allProviders)) {
                select.append('<option value="">Error: proveedores no válidos</option>');
                return;
            }

            if (allProviders.length === 0) {
                select.append('<option value="">No hay proveedores disponibles</option>');
                return;
            }

            // Filtrar proveedores que ya están asociados y no están marcados para eliminar
            const availableProviders = allProviders.filter(provider => {
                const isAlreadyAssociated = currentProviders.some(current => 
                    current.supplier_id === provider.id && !current.to_delete
                );
                return !isAlreadyAssociated;
            });

            if (availableProviders.length === 0) {
                select.append('<option value="">Todos los proveedores ya están asociados</option>');
                return;
            }

            availableProviders.forEach(provider => {
                select.append(`<option value="${provider.id}">${provider.razon_social}</option>`);
            });
        }
        
        // Función para renderizar la lista de proveedores
        function renderProviderList() {
            const container = $('#product_provider_list');
            container.html('');

            // Filtrar proveedores que no están marcados para eliminar
            const visibleProviders = currentProviders.filter(provider => !provider.to_delete);

            if (visibleProviders.length === 0) {
                container.append(`
                    <div class="provider-item">
                        <span class="text-muted">No tiene proveedores asociados</span>
                    </div>
                `);
                return;
            }

            visibleProviders.forEach((provider, index) => {
                // Encontrar el índice real en el array original
                const realIndex = currentProviders.findIndex(p => p.supplier_id === provider.supplier_id);
                container.append(`
                    <div class="provider-item" data-provider-id="${provider.supplier_id}">
                        <span class="provider-name">${provider.supplier_name}</span>
                        <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${realIndex}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
            });
        }

        // Función para agregar proveedor
        function addProvider(supplierId, supplierName) {
            // Verificar si ya existe un proveedor con este ID
            const existingIndex = currentProviders.findIndex(p => p.supplier_id === parseInt(supplierId));
            
            if (existingIndex !== -1) {
                // Si existe y está marcado para eliminar, simplemente desmarcarlo
                if (currentProviders[existingIndex].to_delete) {
                    currentProviders[existingIndex].to_delete = false;
                } else {
                    alert('Este proveedor ya está asociado al producto');
                    return;
                }
            } else {
                // Si no existe, agregarlo como nuevo
                const newProvider = {
                    supplier_id: parseInt(supplierId),
                    supplier_name: supplierName,
                    is_new: true
                };
                currentProviders.push(newProvider);
            }

            renderProviderList();
            updateProviderSelect();
        }

        // Función para remover proveedor
        function removeProvider(index) {
            if (index < 0 || index >= currentProviders.length) {
                return;
            }

            const provider = currentProviders[index];

            // Si el proveedor ya existía, marcarlo para eliminar
            if (!provider.is_new) {
                provider.to_delete = true;
            } else {
                // Si es nuevo, simplemente eliminarlo del array
                currentProviders.splice(index, 1);
            }

            renderProviderList();
            updateProviderSelect();
        }

        // Cargar proveedores al inicializar - MEJORADO
        loadAllProviders();

        // Modal de Editar - MEJORADO
        $('#productos-body').on('click', '.btn-edit', function() {
            var productId = $(this).data('id');

            // Asegurarse de que los proveedores estén cargados antes de abrir el modal
            loadAllProviders().then(function() {
                $.ajax({
                    url: '{{ route('insumos.show', '') }}/' + productId,
                    method: 'GET',
                    success: function(data) {
                        $('#product_id').val(data.product.id);
                        $('#product_name').val(data.product.nombre);
                        $('#unit_price').val(data.product.unit_price);
                        $('#unit_measure').val(data.product.unidad_medida);

                        // Cargar proveedores actuales
                        currentProviders = [];
                        if (data.productProviders && Array.isArray(data.productProviders) && data.productProviders.length > 0) {
                            data.productProviders.forEach(function(provider) {
                                currentProviders.push({
                                    supplier_id: provider.supplier_id,
                                    supplier_name: provider.supplier ? provider.supplier.razon_social : 'Proveedor no disponible',
                                    is_new: false,
                                    to_delete: false
                                });
                            });
                        }
                        renderProviderList();
                        updateProviderSelect();
                        $('#editModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error al cargar los detalles:', xhr.responseText);
                        alert('Error al cargar los detalles del producto');
                    }
                });
            }).catch(function(error) {
                console.error('Error al cargar proveedores:', error);
                alert('Error al cargar los proveedores. Intente nuevamente.');
            });
        });

        // Agregar proveedor
        $('#btn-add-provider').on('click', function() {
            const supplierId = $('#new_provider_select').val();
            const supplierName = $('#new_provider_select option:selected').text();

            if (supplierId && supplierName && supplierName !== 'Seleccione un proveedor') {
                addProvider(supplierId, supplierName);
                $('#new_provider_select').val('');
            } else {
                alert('Por favor seleccione un proveedor válido');
            }
        });

        // Remover proveedor
        $('#product_provider_list').on('click', '.btn-remove', function() {
            const index = parseInt($(this).data('index'));

            if (confirm('¿Está seguro de que desea remover este proveedor?')) {
                removeProvider(index);
            }
        });


        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();

            const productId = $('#product_id').val();
            
            // Obtener el token CSRF directamente del formulario
            const csrfToken = $('#editProductForm input[name="_token"]').val();
            
            // Preparar datos del formulario
            const formData = {
                _token: csrfToken,
                _method: 'PUT',
                product_id: productId,
                product_name: $('#product_name').val(),
                unit_price: $('#unit_price').val(),
                unit_measure: $('#unit_measure').val(),
                category_id: $('input[name="category_id"]').val(),
                providers: JSON.stringify(currentProviders) // Enviar como JSON string
            };

            $.ajax({
                url: '{{ route('insumos.update', '') }}/' + productId,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    console.log('Respuesta exitosa');
                    $('#editModal').modal('hide');
                    
                    // Verificar si la respuesta indica éxito
                    if (response.status) {
                        ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Operación exitosa'
                        }).then(() => {
                            window.location.href = '{{ route('insumos.index') }}';
                        });
                    } else {
                        // Solo mostrar ToastError si hay un error en la respuesta
                        ToastError.fire({
                            text: response.error || 'Ocurrió un error'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error al actualizar:', xhr.responseText);
                    
                    // En caso de error en la respuesta
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        const errorMessages = Object.values(errors).join('\n');
                        alert(errorMessages);
                    } else {
                        ToastError.fire({
                            text: xhr.responseJSON?.message || 'Ocurrió un error'
                        });
                    }
                }
            });
        });

        
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            $('#deleteRawMaterialForm').attr('action', `{{ url('insumos') }}/${id}`);
        });

        $('#textoBusqueda').on('input', function() {
            let query = $(this).val();

            $.ajax({
                url: '{{ route('insumos.filtrar') }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    let rows = '';

                    if (response.insumos && response.insumos.length > 0) {
                        response.insumos.forEach((insumo, index) => {
                            rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${insumo.nombre}</td>
                                <td>${insumo.unidad_medida}</td>
                                <td>${insumo.unit_price}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-show btn-icon" data-id="${insumo.id}">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-edit btn-icon" data-id="${insumo.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="${insumo.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        });
                        $('.d-flex.justify-content-center.mt-3').html('');
                    } else {
                        rows = `<tr><td colspan="5" class="text-center">No se encontraron resultados.</td></tr>`;
                        $('.d-flex.justify-content-center.mt-3').html('');
                    }

                    $('#productos-body').html(rows);
                }
            });
        });

        $('#productos-body').on('click', '.btn-show', function() {
            var id = $(this).data('id');
            $('#tbl-items').html('');

            $.ajax({
                url: '{{ route('insumos.showp', '') }}/' + id,
                method: 'GET',
                success: function(data) {
                    var html = '';

                    if (data.details && Array.isArray(data.details) && data.details.length > 0) {
                        data.details.forEach(function(detail) {
                            html += `
                            <tr>
                                <td>${detail.supplier ? detail.supplier.razon_social : 'N/A'}</td>
                            </tr>
                        `;
                        });
                    } else {
                        html = '<tr><td colspan="1">No tiene proveedores.</td></tr>';
                    }

                    $('#tbl-items').html(html);
                    $('#showModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });
    });
</script>
@endsection