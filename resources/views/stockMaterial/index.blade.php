@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('stockMaterial.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('stockMaterial.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Reporte Paloteo Materiales</h2>
<p>Lista de paloteos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="" class="mt-3" id="fromFilter">
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

                            <div class="col-md-6 mb-3 d-flex flex-nowrap align-items-end gap-2">
                                <button type="submit" class="btn btn-primary" id="btnFiltrar">Filtrar</button>

                                <a href="{{ route('stockMaterial.index') }}" class="btn btn-warning" id="btnLimpiar">
                                    Limpiar
                                </a>

                                <a href="#" class="d-none btn btn-warning d-inline-flex align-items-center text-nowrap" id="btnPDFStock">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF stock final
                                </a>

                                <a href="#" class="btn btn-success d-inline-flex align-items-center text-nowrap" id="btnPDFPaloteo">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF paloteo
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Stock Final</th>
                                        <th>Stock Teórico</th>
                                        <th>Encuadre</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($stock as $s)
                                    <tr>
                                        <td>{{ ($stock->currentPage() - 1) * $stock->perPage() + $loop->iteration }}</td>
                                        <td>{{ $s->product->nombre }}</td>
                                        <td>{{ $s->stock_final }}</td>
                                        <td>{{ $s->venta_teorica }}</td>
                                        <td>
                                            {{ $s->encuadre == 0 ? 'Sí' : 'No' }}
                                        </td>
                                        <td>{{ $s->fecha->format('d/m/Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay paloteos registrados.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $stock->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('fromFilter');
        const buttonFiltrar = document.getElementById('btnFiltrar');
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

    document.getElementById('btnPDFStock').addEventListener('click', function(e) {
        e.preventDefault();

        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;
        const turno = document.querySelector('select[name="turno"]').value;

        if (turno === "") {
            ToastMessage.fire({ icon: 'error', text: 'Debe seleccionar un turno' });
            return;
        }

        let url = "{{ route('paloteo.pdf', [
            'startDate' => 'START',
            'endDate' => 'END',
            'sede' => 'SEDE',
            'turno' => 'TURNO'
        ])
        }}";

        // Fechas por defecto del mes actual
        const defaultStart = "{{ now()->startOfMonth()->format('Y-m-d') }}";
        const defaultEnd = "{{ now()->endOfMonth()->format('Y-m-d') }}";

        // Reemplazar en la URL
        url = url.replace('START', startDate || defaultStart);
        url = url.replace('END', endDate || defaultEnd);
        url = url.replace('SEDE', sede || 'null');
        url = url.replace('TURNO', turno || 'null');

        window.open(url, '_blank');
    });



    document.getElementById('btnPDFPaloteo').addEventListener('click', function(e) {
        e.preventDefault();

        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        if (startDate === "") {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe seleccionar una fecha'
            });
            return;
        }

        let url = "{{ route('paloteoMaterial.pdfGeneral', ['startDate' => 'START', 'endDate' => 'END'])}}";

    // Fechas por defecto del mes actual
    const defaultStart = "{{ now()->startOfMonth()->format('Y-m-d') }}";
    const defaultEnd = "{{ now()->endOfMonth()->format('Y-m-d') }}";

    // Reemplazar en la URL
    url = url.replace('START', startDate || defaultStart);
    url = url.replace('END', endDate || defaultEnd);

    window.open(url, '_blank');
    });
</script>
@endsection