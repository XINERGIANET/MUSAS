@extends('template.index')

@section('header')
<h1>Almacenamiento por Sede</h1>
<p>Lista de registros de almacenamiento de productos por sede</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <!-- Filtro por Sede -->
                        <form method="GET" action="{{ route('storage4.index') }}">
                            <div class="row d-flex">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="sedeFilter" class="form-label">Filtrar por Sede</label>
                                        {{-- <select id="sedeFilter" class="form-select" name="headquarter_id" onchange="this.form.submit()">
                                            <option value="">Todas las Sedes</option>
                                            @foreach($headquarters as $headquarter)
                                                <option value="{{ $headquarter->id }}" {{ request('headquarter_id') == $headquarter->id ? 'selected' : '' }}>
                                        {{ $headquarter->nombre }}
                                        </option>
                                        @endforeach
                                        </select> --}}
                                        <select id="sedeFilter" class="form-select" name="headquarter_id">
                                            <option value="">Todas las Sedes</option>
                                            @foreach($headquarters as $headquarter)
                                            <option value="{{ $headquarter->id }}" {{ request('headquarter_id') == $headquarter->id ? 'selected' : '' }}>
                                                {{ $headquarter->nombre }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="search-product" class="form-label">Filtrar por Producto</label>
                                        <input hidden type="number" id="product_id" name="product_id" placeholder="">
                                        <input type="text" class="form-control" id="search-product" placeholder="Todos los productos">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="search-presentation" class="form-label">Filtrar por Presentación</label>
                                        <select id="presentationFilter" class="form-select" name="presentation_id">
                                            <option value="">Todas las presentaciones</option>
                                            @foreach($presentations as $presentation)
                                            <option value="{{ $presentation->id }}" {{ request('presentation_id') == $presentation->id ? 'selected' : '' }}>
                                                {{ $presentation->nombre }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="mb-3 w-50s me-2">
                                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                    </div>                                    
                                    <div class="mb-3 w-50s me-2">
                                        <a href="{{ route('storage4.index') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                    </div>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="mb-3 w-50s me-2">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                            <i class="bi bi-plus-circle"></i> Nuevo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="row d-flex">
                            <div class="d-flex justify-content-end align-items-center">
                                <h5>
                                    <strong>TOTAL S/ {{ number_format($total, 2, '.', '') }}</strong>
                                </h5>
                            </div>
                        </div>


                        <!-- Tabla de almacenamiento -->
                        <h4 class="mt-3">Registros de Almacenamiento</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="storageTable">
                                <thead>
                                    <tr>
                                        <th>Sede</th>
                                        <th>Producto</th>
                                        <th>Presentación</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($storageData as $storage)
                                    @php
                                    $precio = $storage->unit_price_real ?? 0;
                                    $cantidad = $storage->quantity;
                                    $subtotal = $precio * $cantidad;
                                    @endphp
                                    <tr>
                                        <td>{{ $storage->headquarter->nombre ?? '—' }}</td>
                                        <td>{{ $storage->product->nombre ?? '—' }}</td>
                                        <td>{{ $storage->product->presentation->nombre ?? '—' }}</td>
                                        <td>{{ number_format($precio, 2) }}</td>
                                        <td>{{ $cantidad }}</td>
                                        <td>{{ number_format($subtotal, 2) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $storage->id ?? 'null' }}"
                                                data-quantity="{{ $cantidad }}"
                                                data-unit_price="{{ $precio }}"
                                                data-product-id="{{ $storage->product_id }}"
                                                data-headquarter-id="{{ $storage->headquarter_id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            @if($storage->id)
                                                <form action="{{ route('storage4.destroy', $storage->id) }}" method="POST" style="display:inline;">
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

                                    @if($storageData->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">No hay datos disponibles para esta sede.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $storageData->links('pagination::bootstrap-4') }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal Editar -->
<div class="modal modal-md fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="editClientForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="product_id" id="modal_product_id">
                <input type="hidden" name="headquarter_id" id="modal_headquarter_id">

                <div class="modal-header">
                    <h5 class="modal-title">Salida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
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
                        <label for="unit_price_out" class="form-label">Precio Nuevo (si cambia)</label>
                        <input type="number" class="form-control" id="unit_price_out" name="unit_price_out"
                            onkeypress="isDecimal(event)" min="0.01" step="0.01">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitEditFormBtn">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal modal-xl fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Registro de producto finalizado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row">
                <div class="col-sm-12">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createProductForm" action="{{ route('products.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="category_id" value="3">
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">Presentación (opcional)</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="presentation_id">
                                        <option value="">Seleccione una presentación</option>
                                        @foreach ($presentaciones as $presentacion)
                                        <option value="{{ $presentacion->id }}">{{ $presentacion->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-start">Unidad de Medida</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="unidad_medida" name="unidad_medida" required>
                                        <option value="">Seleccione una unidad de medida</option>
                                        @foreach ($unidadMedidas as $unidadMedida)
                                        <option value="{{ $unidadMedida->nombre }}">{{ $unidadMedida->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">Categoria (opcional)</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="product_categorie_id" name="product_categorie_id">
                                        <option value="">Seleccione una Categoria</option>
                                        @foreach ($productCategory as $category)
                                        <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-center"> <!-- alineación vertical centrada -->
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <!-- Label centrado vertical y horizontalmente -->
                                    <label for="unit_price" class="col-form-label text-center w-100">
                                        Precio por Sede
                                    </label>
                                </div>
                                <div class="col-md-10">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0" id="productionTable">
                                            <thead>
                                                <tr>
                                                    <th>Sede</th>
                                                    <th>Precio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($sedes as $sede)
                                                <tr>
                                                    <td>{{ $sede->nombre }}</td>
                                                    <td>
                                                        <input type="number"
                                                            id="unit_price_{{ $sede->id }}"
                                                            name="unit_price[{{ $sede->id }}]"
                                                            class="form-control cantidad-input"
                                                            min="0.01"
                                                            step="0.01"
                                                            placeholder="0.00">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                            <!-- <div class="mb-3 row">
                                <label for="unit_price" class="col-sm-3 col-form-label text-start">Precio Unitario</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="unit_price" name="unit_price" step="0.01" required>
                                </div>
                            </div> -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
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

@section('scripts')
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#editModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const id = button.data('id');
            const quantity = parseFloat(button.data('quantity')) || 0;
            const unit_price = parseFloat(button.data('unit_price')) || 0;
            const product_id = button.data('product-id');
            const headquarter_id = button.data('headquarter-id');

            const modal = $(this);
            modal.find('#editClientForm').attr('action', `{{ url('storage4') }}/${id}`);
            modal.find('#quantity').val(quantity);
            modal.find('#unit_price').val(unit_price.toFixed(2));
            modal.find('#quantity_out').val(quantity);
            modal.find('#unit_price_out').val(unit_price.toFixed(2));

            modal.find('#modal_product_id').val(product_id);
            modal.find('#modal_headquarter_id').val(headquarter_id);

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

    var products = @json($products);
    $('#search-product').autocomplete({
        source: function(request, response) {
            var results = $.map(products, function(item) {
                if (item.nombre.toLowerCase().includes(request.term.toLowerCase())) {
                    return {
                        label: item.nombre,
                        value: item.nombre,
                        id: item.id
                    };
                }
            });
            response(results);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#product_id').val(ui.item.id);
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };
</script>
<script>
    $(document).ready(function() {
        $('#editClientForm').on('submit', function() {
            $('#global-spinner').removeClass('spinner-hidden').addClass('spinner-visible');
        });
    });

    $('#createProductForm').on('submit', function(e) {
		e.preventDefault();

		let headquarters = [];

		// Recorremos cada input unit_price para construir el array headquarters
		$('#productionTable tbody tr').each(function() {
			const sedeId = $(this).find('input[name^="unit_price"]').attr('name').match(/\d+/)[0];
			const precioStr = $(this).find('input[name^="unit_price"]').val();
			const precio = parseFloat(precioStr);

			if (!isNaN(precio) && precio > 0) {
				headquarters.push({
					headquarter_id: parseInt(sedeId),
					unit_price: precio
				});
			}
		});

		if (headquarters.length === 0) {
			ToastMessage.fire({
				icon: 'warning',
				text: 'Debe ingresar al menos un precio válido por sede.'
			});
			return;
		}

		// Construir el objeto con los datos del formulario
		let dataToSend = {
			_token: $('input[name="_token"]').val(),
			nombre: $('#nombre').val(),
			category_id: $('input[name="category_id"]').val(),
			presentation_id: $('#presentation_id').val(),
			// observacion: $('#observacion').val(),
			unidad_medida: $('#unidad_medida').val(),
			product_categorie_id: $('#product_categorie_id').val(),
			headquarters: JSON.stringify(headquarters)
		};

		$.ajax({
			url: $(this).attr('action'),
			method: 'POST',
			data: dataToSend,
			success: function(response) {
				if (response.status) {
					ToastMessage.fire({
						icon: 'success',
						text: response.message || 'Producto registrado correctamente.'
					}).then(() => location.reload());
				} else {
					ToastError.fire({
						text: response.error || 'Ocurrió un error.'
					});
				}
			},
			error: function(xhr) {
				const errors = xhr.responseJSON?.errors;
				if (errors) {
					const errorMessages = Object.values(errors).flat().join('\n');
					alert(errorMessages);
				} else {
					ToastError.fire({
						text: xhr.responseJSON?.message || 'Ocurrió un error inesperado.'
					});
				}
			}
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
@endsection