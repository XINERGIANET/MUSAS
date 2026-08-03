@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('products.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('products.index') }}">Historico</a>
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
<h2>Lista de Productos Finalizados</h2>
<p>Listado de productos registrados.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <!-- Filtro por rango de fechas y botones de importar/exportar -->
                    <div class="row mb-3">
                        <!-- Filtro por rango de fechas a la izquierda -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text"
                                    class="form-control"
                                    id="textoBusqueda"
                                    placeholder="Buscar Nombre Producto...">
                            </div>
                        </div>

                        <!-- Botones de importar y exportar a la derecha -->
                        <div class="col-md-6 offset-md-2 text-end">
                            <a href="{{ route('products.pdf', ['category_id' => 3]) }}" class="btn btn-info" type="button" id="btnPDF">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            <a href="{{ route('products.excel', ['category_id' => 3]) }}" class="btn btn-info" type="button" id="btnExportar">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de productos -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Producto</th>
                                    <th>Presentación</th>
                                    <th>Unidad de Medida</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="product-body">
                                @forelse ($products as $product)
                                <tr>
                                    <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->nombre }}</td>
                                    <td>{{ $product->presentation->nombre ?? 'Sin presentación' }}</td>
                                    <td>{{ $product->unidad_medida }}</td>
                                    <td>{{ $product->productCategory->nombre ?? 'Sin Categoria' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-info btn-icon" data-id="{{ $product->id }}" title="Precio por Sede">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        </button>
                                        <!-- Botón para editar -->
                                        <button class="btn btn-sm btn-warning btn-icon"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $product->id }}"
                                            data-nombre="{{ $product->nombre }}"
                                            data-presentation_id="{{ $product->presentation_id }}"
                                            data-product_categorie_id="{{ $product->product_categorie_id }}"
                                            data-unidad_medida="{{ $product->unidad_medida }}" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- Botón para eliminar -->
                                        <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $product->id }}" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay productos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" name="category_id" value="3">
                <div class="modal-body">
                    <!-- Información básica del producto -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nombre" class="form-label">Producto</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_presentation_id" class="form-label">Presentación</label>
                            <select class="form-control" id="edit_presentation_id" name="presentation_id" required>
                                <option value="">Seleccione una Presentación</option>
                                @foreach ($presentaciones as $presentacion)
                                <option value="{{ $presentacion->id }}">{{ $presentacion->nombre }}</option>
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
                        <div class="col-md-6 mb-3">
                            <label for="edit_product_categorie_id" class="form-label">Categoría (opcional)</label>
                            <select class="form-control" id="edit_product_categorie_id" name="product_categorie_id">
                                <option value="">Seleccione una Categoría</option>
                                @foreach ($productCategory as $category)
                                <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Sección de precios por sede -->
                    <div class="border-top pt-4">
                        <h6 class="mb-3">Precios por Sede</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_sede_select" class="form-label">Agregar Sede</label>
                                <select class="form-control" id="edit_sede_select">
                                    <option value="">Seleccione una sede</option>
                                    @foreach ($headquarters as $headquarter)
                                    <option value="{{ $headquarter->id }}" data-name="{{ $headquarter->nombre }}">
                                        {{ $headquarter->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_precio_input" class="form-label">Precio Unitario</label>
                                <input type="number" class="form-control" id="edit_precio_input" 
                                       placeholder="0.00" step="0.01" min="0.01">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-success btn-sm w-100" id="edit_agregar_sede">
                                    <i class="bi bi-plus"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de sedes agregadas -->
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sede</th>
                                        <th>Precio Unitario</th>
                                        <th width="80">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_sedes_tabla">
                                    <!-- Las filas se agregan dinámicamente -->
                                </tbody>
                            </table>
                            <div id="edit_no_sedes" class="text-muted text-center py-3" style="display: none;">
                                No hay sedes agregadas
                            </div>
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
            <form id="deleteProductForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Producto</h5>
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


<!-- Modal para mostrar Precio por Sede -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
        // Modal de Editar
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');

            $('#editProductForm').attr('action', `{{ url('products') }}/${id}`);

            $('#edit_nombre').val(button.data('nombre'));
            $('#edit_presentation_id').val(button.data('presentation_id'));
            $('#edit_product_categorie_id').val(button.data('product_categorie_id'));
            $('#edit_unidad_medida').val(button.data('unidad_medida'));

            // Limpiar tabla de sedes
            $('#edit_sedes_tabla').empty();

            // Cargar precios por sede existentes
            $.ajax({
                url: '{{ route('products.vps', '') }}/' + id,
                method: 'GET',
                success: function(data) {
                    if (data.details && data.details.length > 0) {
                        data.details.forEach(function(detail) {
                            $('#edit_sedes_tabla').append(`
                                <tr data-sede-id="${detail.headquarter_id}">
                                    <td>${detail.headquarter ? detail.headquarter.nombre : 'N/A'}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm sede-precio-input" value="${detail.unit_price}" min="0.01" step="0.01" />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-sede">Eliminar</button>
                                    </td>
                                    <input type="hidden" name="sedes[${detail.headquarter_id}][id]" value="${detail.headquarter_id}">
                                </tr>
                            `);
                        });
                        $('#edit_no_sedes').hide();
                    } else {
                        $('#edit_no_sedes').show();
                    }
                }
            });
        });

        $('#edit_agregar_sede').on('click', function() {
            const sedeId = $('#edit_sede_select').val();
            const sedeNombre = $('#edit_sede_select option:selected').text();
            const precio = $('#edit_precio_input').val();

            if (!sedeId || !precio) return;

            // Evitar duplicados
            if ($('#edit_sedes_tabla tr[data-sede-id="' + sedeId + '"]').length > 0) return;

            $('#edit_sedes_tabla').append(`
                <tr data-sede-id="${sedeId}">
                    <td>${sedeNombre}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm sede-precio-input" value="${precio}" min="0.01" step="0.01" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-sede">Eliminar</button>
                    </td>
                    <input type="hidden" name="sedes[${sedeId}][id]" value="${sedeId}">
                </tr>
            `);

            $('#edit_no_sedes').hide();
            $('#edit_precio_input').val('');
            $('#edit_sede_select').val('');
        });

        $(document).on('click', '.btn-remove-sede', function() {
            $(this).closest('tr').remove();
            if ($('#edit_sedes_tabla tr').length === 0) {
                $('#edit_no_sedes').show();
            }
        });

        $('#editProductForm').on('submit', function(e) {
            e.preventDefault(); // Evita el envío normal

            // Elimina inputs previos
            $('#editProductForm input[name^="sedes["][name$="[precio]"]').remove();

            // Agrega los precios actuales
            $('#edit_sedes_tabla tr').each(function() {
                const sedeId = $(this).data('sede-id');
                const precio = $(this).find('.sede-precio-input').val();
                $('<input>').attr({
                    type: 'hidden',
                    name: `sedes[${sedeId}][precio]`,
                    value: precio
                }).appendTo('#editProductForm');
            });

            // Enviar por AJAX
            $.ajax({
                url: $('#editProductForm').attr('action'),
                method: 'POST',
                data: $('#editProductForm').serialize(),
                success: function(response) {
                     $('#editModal').modal('hide');
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Operación exitosa'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    ToastError.fire({
                        text: response.error || 'Ocurrió un error'
                    });
                }
            });
        });

        // Modal de Eliminar
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget); // Botón que activó el modal
            const id = button.data('id'); // Obtener el ID del producto

            // Actualizar la acción del formulario con el ID del producto
            $('#deleteProductForm').attr('action', `{{ url('products') }}/${id}`);
        });

        $('#textoBusqueda').on('input', function() {
            let query = $(this).val();

            if (query === '') {
                window.location.href = "{{ route('products.index') }}";
                return;
            }

            $.ajax({
                url: '{{ route("buscar-producto.filtro") }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    let rows = '';

                    if (response.products.length > 0) {
                        response.products.forEach((product, index) => {
                            rows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${product.nombre}</td>
                            <td>${product.presentation && product.presentation.nombre ? product.presentation.nombre : 'Sin presentación'}</td>
                            <td>${product.unidad_medida}</td>
                            <td>${product.product_category && product.product_category.nombre ? product.product_category.nombre : 'Sin Categoria'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-info btn-icon" data-id="${product.id}" title="Precio por Sede">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </button>
                                <button class="btn btn-sm btn-warning btn-icon"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="${product.id}"
                                    data-nombre="${product.nombre}"
                                    data-presentation_id="${product.presentation_id}"
                                    data-product_categorie_id="${product.product_categorie_id}"
                                    data-unidad_medida="${product.unidad_medida}" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    data-id="${product.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                        });
                    } else {
                        rows = `<tr><td colspan="8" class="text-center">No se encontraron resultados.</td></tr>`;
                    }

                    $('#product-body').html(rows);
                    $('.d-flex.justify-content-center.mt-3').html(''); // Limpiar paginación
                }
            });
        });


        $(document).on('click','.btn-info', function() {
            var id = $(this).data('id'); // Obtener el ID del producto

            // Limpiar la tabla de detalles
            $('#tbl-items').html('');

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
</script>
@endsection
