@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif
@endsection

@section('header')
<h2>Egresos de Caja</h2>
<p>Lista de egresos de caja</p>
@endsection

@section('content')

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">

                    <form method="GET" class="row mb-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Desde:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Hasta:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Motivo</th>
                                    <th>Monto</th>
                                    <th class="d-none">Cantidad</th>
                                    <th>Descripción</th>
                                    <th>N° Comprobante</th>
                                    <th class="d-none">Monto</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $egr)
                                @php $rowspan = $egr->details->count(); @endphp
                                @foreach($egr->details as $i => $det)
                                <tr>
                                    @if($i === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $egr->created_at ? $egr->created_at->format('Y-m-d H:i') : '' }}</td>
                                    @endif
                                    <td>{{ $det->product->nombre ?? '-' }}</td>
                                    <td>S/ {{ number_format($det->unit_price, 2) }}</td>
                                    <td class="d-none">{{ $det->quantity }}</td>
                                    <td>{{ $egr->description }}</td>
                                    @if($i === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $egr->invoice_number }}</td>
                                    <td rowspan="{{ $rowspan }}" class="d-none">S/ {{ number_format($egr->details->sum('subtotal'), 2) }}</td>
                                    <td rowspan="{{ $rowspan }}">
                                        <button type="button" class="btn btn-danger btn-sm"><i class="bi bi-trash-fill"></i></button>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay egresos en el rango seleccionado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Aquí puedes agregar scripts para el botón ver-detalle si lo necesitas -->
@endsection