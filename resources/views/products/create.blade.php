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

@section('header')
<h2>Registrar Productos Finalizados</h2>
<p>Complete el formulario para registrar un nuevo producto.</p>
@endsection


@section('styles')
<style>
	#btnImportar {
		margin: 0 10px !important;
	}
</style>
@endsection


@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body p-3">
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
									<select class="form-control border-dark" id="presentation_id" name="presentation_id">
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
								<button data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-info" type="button" id="btnImportar">
									<i class="bi bi-file-earmark-excel"></i> Importar
								</button>
								<button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Importar -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="importForm" method="POST">
				@csrf
				<input type="hidden" name="category_id" value="3">
				<div class="modal-header">
					<h5 class="modal-title">Importar</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p><a href="{{ asset('assets/xlsx/PlantillaProductos.xlsx') }}" target="_blank">Descargar plantilla de ejemplo</a></p>
					<div class="mb-3">
						<label class="form-label">Archivo de plantilla *</label>
						<input type="file" class="form-control" name="file" id="file" autocomplete="off">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
	style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050;">
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
@endsection


@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('importForm');
		const buttonFiltrar = document.getElementById('btn-save');
		const spinner = document.getElementById('global-spinner');

		let clickedFiltrar = false;

		spinner.classList.remove('spinner-visible');
		spinner.classList.add('spinner-hidden');

		buttonFiltrar.addEventListener('click', function() {
			clickedFiltrar = true;
		});

		form.addEventListener('submit', function() {
			if (clickedFiltrar) {
				spinner.classList.remove('spinner-hidden');
				spinner.classList.add('spinner-visible');
			}

			clickedFiltrar = false;
		});
	});

	$('#importForm').submit(function(e) {
		e.preventDefault();

		var fd = new FormData();

		fd.append('file', $('#file')[0].files[0]);
		fd.append('category_id', $('input[name="category_id"]').val());

		$.ajax({
			url: '{{ route('products.import') }}',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			processData: false,
			contentType: false,
			data: fd,
			success: function(data) {
				if (data.status) {
					$('#importModal').modal('hide');
					$('#importForm')[0].reset();

					ToastMessage.fire({
							text: 'Archivo importado'
						})
						.then(() => location.reload());
				} else {
					ToastError.fire({
						text: data.error ? data.error : 'Ocurrió un error'
					});
				}
			},
			error: function(err) {
				ToastError.fire({
					text: 'Ocurrió un error'
				});
				console.log(err)
			}
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
			observacion: $('#observacion').val(),
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
					}).then(() => {
						window.location.href = '{{ route("products.index") }}';
					});
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



</script>
@endsection