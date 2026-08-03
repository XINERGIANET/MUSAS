@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('miscelaneo.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('miscelaneo.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Productos Misceláneos</h2>
<p>Lista de productos misceláneos</p>
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
                                <input type="text" class="form-control" id="textoBusqueda" placeholder="Buscar Nombre ...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productos-body">
                                @forelse ($misc as $product)
                                <tr>
                                    <td>{{ ($misc->currentPage() - 1) * $misc->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->nombre }}</td>
                                    <td>
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
                                    <td colspan="4" class="text-center">No hay Productos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $misc->links('pagination::bootstrap-4') }}
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
                    <h5 class="modal-title">Eliminar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este Producto?</p>
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
      
        // Modal de Editar - MEJORADO
        $('#productos-body').on('click', '.btn-edit', function() {
            var productId = $(this).data('id');
            
            $.ajax({
                url: '{{ route('miscelaneo.show', '') }}/' + productId,
                method: 'GET',
                success: function(data) {
                    $('#product_id').val(data.product.id);
                    $('#product_name').val(data.product.nombre);
                    $('#editModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar los detalles:', xhr.responseText);
                    alert('Error al cargar los detalles del producto');
                }
            });
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
            };

            $.ajax({
                url: '{{ route('miscelaneo.update', '') }}/' + productId,
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
                            window.location.href = '{{ route('miscelaneo.index') }}';
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
            $('#deleteRawMaterialForm').attr('action', `{{ url('miscelaneo') }}/${id}`);
        });

        $('#textoBusqueda').on('input', function() {
            let query = $(this).val();

           $.ajax({
                url: '{{ route('miscelaneo.filtrar') }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    let rows = '';

                    if (response.miscelaneos && response.miscelaneos.length > 0) {
                        response.miscelaneos.forEach((misc, index) => {
                            rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${misc.nombre}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit btn-icon" data-id="${misc.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-icon" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-id="${misc.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        });
                    } else {
                        rows = `<tr><td colspan="5" class="text-center">No se encontraron resultados.</td></tr>`;
                    }

                    $('#productos-body').html(rows);

                    let pagination = '';
                    if (response.pagination && response.pagination.last_page > 1) {
                        for (let i = 1; i <= response.pagination.last_page; i++) {
                            pagination += `
                            <li class="page-item ${response.pagination.current_page === i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>`;
                        }
                    }

                    $('.d-flex.justify-content-center.mt-3').html(`
                        <ul class="pagination">
                            ${pagination}
                        </ul>
                    `);

                    $('.page-link').click(function (e) {
                        e.preventDefault();
                        let page = $(this).data('page');
                        loadPage(page, query); 
                    });
                }
            });
        });

        
    });
</script>
@endsection