@extends('template.index')

@section('nav')
<x-nav-sales />
@endsection

@section('header')
<h2>Producción del día</h2>
<p>{{ $sedeFiltro }} - {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                
                <!-- Solo mostrar el total -->
                <div class="card-body d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>
                            Producción de hoy
                        </h5>
                        <small class="text-muted">{{ $sedeFiltro }}</small>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0"><strong>TOTAL S/ {{ number_format($total, 2, '.', '') }}</strong></h5>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</small>
                    </div>
                </div>

                <!-- Tabla simple -->
                <div class="card-body p-3">
                    @if($productions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table">
                                <tr>
                                    <th>Producto Terminado</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Precio Unit.</th>
                                    <th class="text-center">Subtotal</th>
                                    <th class="text-center">Turno</th>
                                    <th class="text-center">Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productions as $production)
                                    @foreach($production->movementDetails as $detail)
                                        @php
                                        $precioSede = $detail->product->productSede
                                            ->where('headquarter_id', $production->headquarter_id)
                                            ->first();
                                        $precio = $precioSede ? $precioSede->unit_price : $detail->product->unit_price;
                                        $cantidad = $detail->quantity;
                                        $subtotal = $precio * $cantidad;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $detail->product->nombre }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span>{{ number_format($cantidad, 0) }}</span>
                                            </td>
                                            <td class="text-center">S/ {{ number_format($precio, 2) }}</td>
                                            <td class="text-center">
                                                <strong>S/ {{ number_format($subtotal, 2) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="{{ $production->turno == 0 ? '' : '' }}">
                                                    {{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}
                                                </span>
                                            </td>
                                            <td class="text-center text-muted">
                                                {{ \Carbon\Carbon::parse($production->created_at)->format('H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <!-- Estado vacío -->
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <h4 class="text-muted mt-3">No hay producción registrada hoy</h4>
                        <p class="text-muted">Aún no se ha registrado producción para el día de hoy en {{ $sedeFiltro }}.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@endsection
