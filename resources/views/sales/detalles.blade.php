@extends('template.index')

@section('nav')

<x-nav-sales />


@endsection

@section('header')
<h2>Detalles Producto</h2>
<p>Lista detalles de productos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body border-bottom">
                    <form action="" id="fromFilter">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ request()->start_date ? request()->start_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ request()->end_date ? request()->end_date : '' }}">
                                </div>
                            </div> 
                            <div class="col d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <!-- <div class=" w-50s me-2">
                                    <button type="button" class="btn btn-success w-100" id="btnExcel">Excel</button>
                                </div> -->
                                <div class="mb-3 w-50s me-2">
                                    <a href="{{ route('sales.detalles') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha Entrega</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(isset($detalles) && count($detalles) > 0)
                                @foreach($detalles as $detalle)
                                    <tr>
                                        <td>{{ $detalle['fecha_entrega'] ?? '-' }}</td>
                                        <td>{{ $detalle['producto'] ?? '-' }}</td>
                                        <td>{{ $detalle['cantidad'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">No hay datos para mostrar</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Spinner de carga -->
<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden" 
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050">
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

    .swal-confirm-btn {
        background-color: #dc3545 !important; /* rojo Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        margin-right: 10px;
        font-weight: 500;
    }

    .swal-cancel-btn {
        background-color: #6c757d !important; /* gris Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-weight: 500;
    }
</style>
<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
        z-index: 2000 !important;
    }
    .ver-foto-disabled {
        color: #aaa !important;
        pointer-events: none;
        text-decoration: none !important;
        cursor: not-allowed;
    }
</style>
@endsection