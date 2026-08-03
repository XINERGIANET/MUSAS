@extends('template.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css') }}" />
@endsection

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

@endsection

@section('header')
<h2>Registro de retoque</h2>
<p>Complete el formulario para registrar un nuevo retoque</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body p-3">
					<div class="header-title w-100">
						<!-- Formulario de registro -->
						<form id="createRetouchForm" action="{{ route('retouch.store') }}" method="POST">
							@csrf

							<div class="mb-3 row">
								<label for="producto_id" class="col-sm-3 col-form-label text-start">Producto</label>
								<div class="col-sm-3">
									<input hidden type="number" class="form-control border-dark" name="producto_id" id="producto_id">
									<input type="text" class="form-control border-dark" name="name" id="search-product" placeholder="Buscar producto">
								</div>

								<label class="col-sm-3 col-form-label text-start">Cantidad</label>
								<div class="col-sm-3">
									<input type="number" class="form-control border-dark" id="cantidad" name="cantidad" required>
								</div>

							</div>

							<div class="mb-3 row">
								<label class="col-sm-3 col-form-label text-start">Fecha</label>
								<div class="col-sm-3">
									<input type="date" class="form-control border-dark" id="fecha" name="fecha" required>
								</div>

								@php
								$isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia') ? true : false;
								$hq = auth()->user()->headquarter ? auth()->user()->headquarter->id : -1;
								@endphp
								<label @if(!$isAdmin) hidden @endif for="sede_id" class="col-sm-3 col-form-label text-start">Sede</label>
								<div @if(!$isAdmin) hidden @endif class="col-sm-3">
									<select class="form-control border-dark" id="sede_id" name="sede_id" required>
										<option value="">Seleccione una sede</option>
										@foreach ($sedes as $sede)
										<option
											@if($hq==$sede->id) selected @endif
											value="{{ $sede->id }}">{{ $sede->nombre }}</option>
										@endforeach
									</select>
								</div>

								@php
								$ocultarTurno = !$isAdmin ? 'd-none' : '';
								@endphp
								<label for="turno" class="col-sm-3 col-form-label {{ $ocultarTurno }}" style="margin-top:1rem !important">Turno</label>
								<div class="col-sm-3 {{ $ocultarTurno }}" style="margin-top:1rem !important">
									<select class="form-control border-dark" id="turno" name="turno" required>
										<option value="">Seleccione un turno</option>
										<option value="0" {{ auth()->user()->turno == 0 ? 'selected' : '' }}>Mañana</option>
										<option value="1" {{ auth()->user()->turno == 1 ? 'selected' : '' }}>Tarde</option>
									</select>
								</div>

							</div>

							<div class="d-flex justify-content-end">
								<button type="submit" class="btn btn-primary">Guardar</button>
							</div>

						</form>
						<hr>

						<form method="GET" action="{{ route('retouch.index') }}" class="mb-4">
							<div class="row">
								<!-- Filtro por fecha de inicio -->
								<div class="col-md-3">
									<label for="start_date">Fecha inicio</label>
									<input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
								</div>

								<!-- Filtro por fecha de fin -->
								<div class="col-md-3">
									<label for="end_date">Fecha fin</label>
									<input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
								</div>

								<!-- Filtro por turno -->
								<div class="col-md-3">
									<label for="turno">Turno</label>
									<select name="turno" id="turno" class="form-control">
										<option value="">Seleccionar turno</option>
										<option value="0" {{ request('turno') == '0' ? 'selected' : '' }}>Mañana</option>
										<option value="1" {{ request('turno') == '1' ? 'selected' : '' }}>Tarde</option>
									</select>
								</div>

								<!-- Filtro por sede -->
								<div class="col-md-3">
									<label for="sede_id">Sede</label>
									<select name="sede_id" id="sede_id" class="form-control">
										<option value="">Seleccionar sede</option>
										@foreach($sedes as $sede)
										<option value="{{ $sede->id }}" {{ request('sede_id') == $sede->id ? 'selected' : '' }}>
											{{ $sede->nombre }}
										</option>
										@endforeach
									</select>
								</div>
							</div>
							<br>

							<div class="d-flex justify-content-end">
								<button type="submit" class="btn btn-primary">Filtrar</button>
							</div>
						</form>


						<div class="row d-flex">
							<div class="d-flex justify-content-end align-items-center mb-3">
								<h5>
									<strong>TOTAL S/ {{ number_format($total, 2, '.', '') }}</strong>
								</h5>
							</div>
						</div>

						<!-- Tabla de productos agregados -->
						<div class="table-responsive">
							<table class="table table-bordered table-striped" id="purchaseTable">
								<thead class="table">
									<tr>
										<th>Producto</th>
										<th>Cantidad</th>
										<th>Precio</th>
										<th>Total</th>
										<th>Sede</th>
										<th>Turno</th>
										<th>Fecha</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($movements as $movement)
									@php
									$firstDetail = $movement->movementDetails->first();
									@endphp
									<tr>
										<td>{{ $firstDetail && $firstDetail->product ? $firstDetail->product->nombre : 'Sin producto' }}</td>
										<td>{{ $firstDetail ? $firstDetail->quantity : 0 }}</td>
										<td>{{ $firstDetail->unit_price ? number_format($firstDetail->unit_price, 2) : '0.00' }}</td>
										<td>{{ number_format($firstDetail->quantity * ($firstDetail->unit_price ?? 0), 2) }}</td>
										<td>{{ $movement->headquarter->nombre }}</td>
										<td>{{ $movement->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
										<td>{{ $movement->date }}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
							<div class="d-flex justify-content-center mt-3">
								{{ $movements->links('pagination::bootstrap-4') }}
							</div>
						</div>


					</div>
				</div>
			</div>
		</div>
	</div>
</div>





@endsection

@section('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const switchTurno = document.getElementById('switchTurno');
		const selectTurno = document.getElementById('turno');

		if (switchTurno && selectTurno) {
			// Establecer el valor inicial basado en el estado del switch
			selectTurno.value = switchTurno.checked ? '1' : '0';

			// Escuchar cambios para mantenerlos sincronizados
			switchTurno.addEventListener('change', function() {
				selectTurno.value = this.checked ? '1' : '0';
			});
		}
	});
</script>

<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script>
	document.getElementById('fecha').valueAsDate = new Date();

	var products = @json($products);
	$('#search-product').autocomplete({
		source: function(request, response) {
			// Filtra los productos localmente
			var results = $.map(products, function(item) {
				if (item.nombre.toLowerCase().includes(request.term.toLowerCase()) &&
					(item.category_id === 3)) {
					return {
						label: item.nombre, // Nombre del producto
						value: item.nombre, // Valor seleccionado
						id: item.id // ID del producto
					};
				}
			});
			response(results);
		},
		appendTo: '.container-fluid',
		select: function(event, ui) {
			$('#producto_id').val(ui.item.id);
		}
	}).autocomplete("instance")._renderItem = function(ul, item) {
		return $("<li>")
			.append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
			.appendTo(ul);
	};

	$('#createRetouchForm').on('submit', function(e) {
		e.preventDefault();
		var formData = $(this).serialize();
		$.ajax({
			url: $(this).attr('action'),
			method: $(this).attr('method'),
			data: formData,
			success: function(response) {

				ToastMessage.fire({
					text: 'Registro guardado'
				}).then(() => location.reload());
				$('#createRetouchForm')[0].reset();


			},
			error: function(xhr) {
				ToastError.fire({
					text: 'Ocurrió un error'
				});
			}
		});
	});
</script>
@endsection