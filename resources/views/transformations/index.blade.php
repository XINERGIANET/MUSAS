@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

@endsection

@section('header')
<h2>Transformar Producto</h2>
<p>Se restará el stock de un producto y se añadirá al producto transformado.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body p-3">
					<div class="header-title w-100">
						@if ($errors->any())
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
						@endif
						<form id="createProductForm" action="{{ route('transformations.store') }}" method="POST">
							@csrf

							<div class="mb-3 row">
								<label for="base" class="col-sm-3 col-form-label text-start">Base</label>
								<div class="col-sm-3">
									<select class="form-control border-dark" id="base" name="base" required>
										<option value="">Seleccione un producto</option>
										@foreach ($products as $product)
										<option value="{{ $product->id }}">{{ $product->nombre }}</option>
										@endforeach
									</select>
								</div>
								<label for="cant_base" class="col-sm-3 col-form-label text-start">Cantidad</label>
								<div class="col-sm-3">
									<input type="number" class="form-control border-dark" id="cant_base" name="cant_base" required>
								</div>
							</div>

							<div class="mb-3 row">
								<label for="transformado" class="col-sm-3 col-form-label text-start">Transformación</label>
								<div class="col-sm-3">
									<select class="form-control border-dark" id="transformado" name="transformado" required>
										<option value="">Seleccione un producto</option>
										@foreach ($portions as $portion)
										<option value="{{ $portion->id }}">{{ $portion->nombre }}</option>
										@endforeach
									</select>
								</div>
								<label for="cant_transformado" class="col-sm-3 col-form-label text-start">Cantidad</label>
								<div class="col-sm-3">
									<input type="number" class="form-control border-dark" id="cant_transformado" name="cant_transformado" required>
								</div>
							</div>

							<div class="mb-3 row">
								<label class="col-sm-3 col-form-label text-start">Fecha</label>
								<div class="col-sm-3">
									<input type="date" class="form-control border-dark" id="date" name="date" required>
								</div>
								@php
								$isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia') ? true : false;
								$hq = auth()->user()->headquarter ? auth()->user()->headquarter->id : -1;
								@endphp

								<label @if(!$isAdmin) hidden @endif for="headquarter_id" class="col-sm-3 col-form-label text-start">Sede</label>
								<div @if(!$isAdmin) hidden @endif class="col-sm-3">
									<select class="form-control border-dark" id="headquarter_id" name="headquarter_id" required>
										<option value="">Seleccione una sede</option>
										@foreach ($headquarters as $headquarter)
										<option
											@if($hq==$headquarter->id) selected @endif
											value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
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
								<button type="submit" class="btn btn-primary">Transformar</button>
							</div>
						</form>
						<hr>
						<form method="GET" action="{{ route('transformations.create') }}" class="mb-4">
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
									<label for="headquarter_id">Sede</label>
									<select name="headquarter_id" id="headquarter_id" class="form-control">
										<option value="">Seleccionar Sede</option>
										@foreach($headquarters as $headquarter)
											<option value="{{ $headquarter->id }}" {{ request('headquarter_id') == $headquarter->id ? 'selected' : '' }}>
												{{ $headquarter->nombre }}
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

						<div class="table-responsive">
							<table class="table table-bordered table-striped" id="purchaseTable">
								<thead>
									<tr>
										<th>Sede</th>
										<th>P. base</th>
										<th>Cantidad</th>
										<th>P. transformado</th>
										<th>Cantidad</th>
										<th>Turno</th>
										<th>Fecha</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($transformations as $transformation)
									<tr>
										<td>{{ $transformation->headquarter->nombre }}</td>
										<td>
											{{ optional($transformation->movementDetails->firstWhere('transformado', 0))->product->nombre ?? '-' }}
										</td>
										<td>
											{{ optional($transformation->movementDetails->firstWhere('transformado', 0))->quantity ?? '-' }}
										</td>
										<td>
											{{ optional($transformation->movementDetails->firstWhere('transformado', 1))->product->nombre ?? '-' }}
										</td>
										<td>
											{{ optional($transformation->movementDetails->firstWhere('transformado', 1))->quantity ?? '-' }}
										</td>
										<td>{{ $transformation->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
										<td>{{ $transformation->date }}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
							<div class="d-flex justify-content-center mt-3">
								{{ $transformations->links('pagination::bootstrap-4') }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	document.getElementById('date').valueAsDate = new Date();
</script>

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
@endsection