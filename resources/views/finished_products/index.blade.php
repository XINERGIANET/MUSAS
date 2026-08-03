@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('finished_products.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('finished_products.index') }}">Historico</a>
    </li>
</ul>
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
</style>
@endsection

@section('header')
<h2>Productos Industrializados</h2>
<p>Lista de productos industrializados</p>
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
                                <input type="text" class="form-control" id="textoBusquedaP" placeholder="Buscar Nombre Materia Prima...">
                            </div>
                        </div>

                        <!-- Botones de importar y exportar a la derecha -->
                        <div class="col-md-6 offset-md-2 text-end">
                            <a href="{{ route('products.pdf', ['category_id' => 2]) }}" class="btn btn-info" type="button" id="btnPDF">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            <a href="{{ route('products.excel', ['category_id' => 2]) }}" class="btn btn-info" type="button" id="btnExportar">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Unidad de Medida</th>
                                    <th>Precio de Compra</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="fproductos-body">
                                @forelse ($products as $product)
                                <tr>
                                    <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->nombre }}</td>
                                    <td>{{ $product->productCategory->nombre ?? 'Sin Categoria' }}</td>
                                    <td>{{ $product->unidad_medida }}</td>
                                    <td>{{ $product->unit_price ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-info btn-icon" data-id="{{ $product->id }}" title="Precio por Sede">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary btn-show btn-icon" data-id="{{ $product->id }}" title="Ver Detalle">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <!--Botón para editar-->
                                        <button class="btn btn-sm btn-warning btn-edit btn-icon" data-id="{{ $product->id }}" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $product->id }}" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay productos industrializados registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links('pagination::bootstrap-4') }}
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
            <form id="editFinishedProductsForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Producto Industrializado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" name="category_id" value="2">
                <div class="modal-body row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        {{-- <label for="edit_observacion" class="form-label">Observacion</label>
                        <input type="text" class="form-control" id="edit_observacion" name="observacion" required> --}}
                        <label for="edit_categoria" class="form-label">Categoria (opcional)</label>
                        <select class="form-control" id="edit_product_categorie_id" name="product_categorie_id">
                            <option value="">Seleccione una Categoria</option>
                            @foreach ($productCategory as $category)
                            <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_unidad_medida" class="form-label">Unidad de Medida</label>
                        <select class="form-control" id="edit_unidad_medida" name="unidad_medida" required>
                            <option value="">Seleccione una unidad de medida</option>
                            @foreach ($unidadMedidas as $unidadMedida)
                            <option value="{{ $unidadMedida->nombre }}">{{ $unidadMedida->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- <div class="col-md-6 mb-3">
                        <label for="edit_unit_price" class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" class="form-control" id="edit_unit_price" name="unit_price">
                    </div> -->
                    <!-- <div class="col-md-6 mb-3">
                        <label for="edit_categoria" class="form-label">Proovedor</label>
                        <select class="form-control" id="edit_supplier_id" name="supplier_id" required>
                            <option value="">Seleccione un Proovedor</option>
                            @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_categoria" class="form-label">Proovedor 2</label>
                        <select class="form-control" id="edit_supplier2_id" name="supplier2_id">
                            <option value="">Seleccione un Proovedor</option>
                            @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->razon_social }}</option>
                            @endforeach
                        </select>
                    </div> -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Precios por Sede</label>
                        <div id="sede-price-container">
                            <!-- Aquí se agregan los inputs dinámicamente por JS -->
                        </div>
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
            <form id="deleteFinishedProductsForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Producto Industrializado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este producto?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para mostrar Proovedores -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proovedores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Proovedor</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar Precio por Sede -->
<div class="modal fade" id="priceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Precio por Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sede</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-prices"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(document).on('click','.btn-edit', function() {
            var productId = $(this).data('id');

            // Hacer una solicitud AJAX para obtener los detalles del producto
            $.ajax({
                url: '{{ route("finished_products.show", ":id") }}'.replace(':id', productId),
                method: 'GET',
                success: function(data) {
                    $('#editFinishedProductsForm').attr('action', `/finished_products/${productId}`);

                    $('#edit_nombre').val(data.product.nombre);
                    $('#edit_product_categorie_id').val(data.product.product_categorie_id);
                    $('#edit_unidad_medida').val(data.product.unidad_medida);
                    $('#edit_unit_price').val(data.product.unit_price);

                    // Limpiar la lista de proveedores
                    $('#product_provider_list').html('');

                    // Mostrar proveedores si existen
                    if (data.productProviders && data.productProviders.length > 0) {
                        data.productProviders.forEach(function(provider) {
                            $('#product_provider_list').append(`
                                <li class="list-group-item">
                                    ${provider.supplier ? provider.supplier.razon_social : 'Proveedor no disponible'}
                                </li>
                    `);
                        });
                    } else {
                        $('#product_provider_list').append(`
                    <li class="list-group-item text-danger">
                        No tiene proveedores asociados
                    </li>
                `);
                    } const container = $('#sede-price-container');
                    container.html(''); // Limpiar contenido anterior

                    if (data.prices && data.prices.length > 0) {
                        data.prices.forEach(price => {
                            container.append(`
                                <div class="input-group mb-2">
                                    <span class="input-group-text">${price.headquarter.nombre}</span>
                                    <input type="hidden" name="prices[${price.headquarter_id}][headquarter_id]" value="${price.headquarter_id}">
                                    <input type="number" step="0.01" class="form-control" name="prices[${price.headquarter_id}][unit_price]" value="${price.unit_price ?? ''}">
                                </div>
                            `);
                        });
                    } else {
                        container.html('<p class="text-muted">No hay precios registrados por sede.</p>');
                    }

                    // Mostrar el modal
                    $('#editModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID de la materia prima

            // Actualizar la acción del formulario con el ID de la materia prima
            $('#deleteFinishedProductsForm').attr('action', `{{ url('finished_products') }}/${id}`);
        });

        $('#textoBusquedaP').on('input', function() {
            let query = $(this).val();

            if (query === '') {
                window.location.href = "{{ route('finished_products.index') }}";
            }

            $.ajax({
                url: '{{ route("finished-products.filtro") }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    console.log(response); // ← ¿Aparece algo?
                    let rows = '';

                    if (response.products.length > 0) {
                        response.products.forEach((product, index) => {
                            rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${product.nombre}</td>
                                <td>${product.product_category && product.product_category.nombre ? product.product_category.nombre : 'Sin Categoria'}</td>
                                <td>${product.unidad_medida}</td>
                                <td>${product.unit_price ?? '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-info btn-icon" data-id="${product.id}" title="Precio por Sede">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary btn-show btn-icon" data-id="${product.id}" title="Ver Detalle">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-edit btn-icon" data-id="${product.id}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="${product.id}" title="Eliminar">
                                        <i class="bi bi-trash"></i>
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

                    $('#fproductos-body').html(rows);
                }
            });
        });

        $(document).on('click','.btn-show', function() {
            var id = $(this).data('id'); // Obtener el ID de la compra

            // Limpiar la tabla de detalles
            $('#tbl-items').html('');

            // Hacer una solicitud AJAX para obtener los detalles
            $.ajax({
                url: '{{ route('finished_products.shown', '') }}/' + id, // Ruta corregida
                method: 'GET',
                success: function(data) {
                    var html = '';

                    // Verifica si hay detalles
                    if (data.details && data.details.length > 0) {
                        // Si hay detalles, mostrar los proveedores
                        data.details.forEach(function(detail) {
                            html += `
                                <tr>
                                    <td>${detail.supplier ? detail.supplier.razon_social : 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        // Si no hay detalles, mostrar un mensaje
                        html = '<tr><td colspan="1">No tiene proveedores.</td></tr>';
                    }

                    $('#tbl-items').html(html);

                    // Mostrar el modal
                    $('#showModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });
        
        $(document).on('click','.btn-info', function() {
            var id = $(this).data('id'); // Obtener el ID del producto

            // Limpiar la tabla de detalles
            $('#tbl-prices').html('');

            // Hacer una solicitud AJAX para obtener los detalles
            $.ajax({
                url: '{{ route('products.vps', '') }}/' + id, // Ruta corregida
                method: 'GET',
                success: function(data) {
                    var html = '';

                    // Verifica si hay detalles
                    if (data.details && data.details.length > 0) {
                        // Si hay detalles, mostrar los proveedores
                        data.details.forEach(function(detail) {
                            html += `
                                <tr>
                                    <td>${detail.headquarter ? detail.headquarter.nombre : 'N/A'}</td>
                                    <td>${detail.unit_price ?? 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        // Si no hay detalles, mostrar un mensaje
                        html = '<tr><td colspan="2">No tiene precio por sede.</td></tr>';
                    }

                    $('#tbl-prices').html(html);

                    // Mostrar el modal
                    $('#priceModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });

    });
</script>

@endsection