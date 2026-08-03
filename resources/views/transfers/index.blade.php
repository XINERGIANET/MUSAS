@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

@endsection

@section('header')
<h2>Registrar Traslado</h2>
<p>Complete el formulario para registrar un nuevo traslado entre sedes.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="formTransfer" action="{{ route('transfers.store') }}" method="POST">
                            @csrf

                            @php
                            $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia') ? true : false;
                            $hq = auth()->user()->headquarter ? auth()->user()->headquarter->id : -1;
                            @endphp
                            <div class="mb-3 row">
                                <label @if(!$isAdmin) hidden @endif for="headquarter_id" class="col-sm-3 col-form-label text-start">Sede Origen</label>
                                <div @if(!$isAdmin) hidden @endif class="col-sm-3">
                                    <select class="form-control border-dark" name="headquarter_id" required>
                                        <option value="">Seleccione una sede origen</option>
                                        @foreach ($headquarters as $headquarter)
                                        <option
                                            @if($hq==$headquarter->id) selected @endif
                                            value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="headquarter_to_id" class="col-sm-3 col-form-label text-start">Sede Destino</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" name="headquarter_to_id" required>
                                        <option value="">Seleccione una sede destino</option>
                                        @foreach ($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Producto -->
                            <div class="mb-3 row">
                                <label for="product_id" class="col-sm-3 col-form-label text-start">Producto</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" name="product_id" required>
                                        <option value="">Seleccione un producto</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="quantity" class="col-sm-3 col-form-label text-start">Cantidad</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" name="quantity" placeholder="Ingrese la cantidad" min="1" required>
                                </div>
                            </div>


                            <!-- Fecha -->
                            <div class="mb-3 row">
                                <label for="date" class="col-sm-3 col-form-label text-start">Fecha</label>
                                <div class="col-sm-3">
                                    <input type="date" class="form-control border-dark" name="date" required value="{{ date('Y-m-d') }}">
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

                    </div>

                    <!-- Botón -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Agregar Traslado</button>
                    </div>
                    </form>

                    <hr>

                    <form method="GET" action="{{ route('transfers.create') }}" class="mb-4">
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
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Turno</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->headquarter->nombre }}</td>
                                    <td>{{ $transfer->headquarter_to->nombre }}</td>
                                    <td>
                                        {{ $transfer->movementDetails->first()->product->nombre ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $transfer->movementDetails->first()->quantity ?? '-' }}
                                    </td>
                                    <td>{{ $transfer->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                    <td>{{ $transfer->date }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $transfers->links('pagination::bootstrap-4') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

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