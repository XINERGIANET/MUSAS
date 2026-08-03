@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary" href="{{ route('production.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary active" href="{{ route('production.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Historial de Producción</h2>
<p>Registros de producción realizados</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="">
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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Sede</label>
                                    <select class="form-select" id="headquarter_id" name="headquarter_id">
                                        <option value="">Seleccione una sede</option>
                                        @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ request('headquarter_id') == $sede->id ? 'selected' : '' }}>
                                            {{ $sede->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Filtro de Turno --}}
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Turno</label>
                                    <select class="form-select" name="turno">
                                        <option value="">Todos los turnos</option>
                                        <option value="0" {{ request('turno') === '0' ? 'selected' : '' }}>Mañana</option>
                                        <option value="1" {{ request('turno') === '1' ? 'selected' : '' }}>Tarde</option>
                                    </select>
                                </div>
                            </div>

                            <!-- <div class="col-md-8 d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                                <a href="#" class="mb-3 btn btn-info me-2" type="button" id="btnPDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                                <a href="#" class="mb-3 btn btn-danger me-2" type="button" id="btnPDFanticipos">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF GENERAL
                                </a>
                                <a href="#" class="mb-3 btn btn-warning me-2" type="button" id="btnPDFResumen">
                                    <i class="bi bi-file-earmark-text"></i> PDF Resumen
                                </a>
                            </div> -->
                            <div class="col-md-8 d-flex align-items-end">
                                <div class="mb-3 me-2">
                                    <button type="submit" class="btn btn-primary">Filtrar</button>
                                </div>
                                <div class="mb-3 me-2">
                                    <a href="{{ route('production.index') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>

                                <div class="btn-group mb-3">
                                    <button type="button" class="btn btn-danger dropdown-toggle"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-file-pdf"></i> INFORMES PDF
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" id="btnPDF">
                                                <i class="bi bi-file-earmark-text"></i> PRODUCCIÓN TOTAL
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="btnPDFanticipos">
                                                <i class="bi bi-file-earmark-text"></i> PRODUCCIÓN SEPARADA POR TIPO
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="btnPDFResumen">
                                                <i class="bi bi-file-earmark-text"></i> RESUMEN PRODUCCIÓN TOTAL
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="card-body d-flex justify-content-end">
                    <div class="row">
                        <h5><strong>TOTAL S/ {{ number_format($total, 2, '.', '') }}</strong></h5>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Producto Terminado</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Turno</th>
                                    <th>Registrado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productions as $production)
                                @foreach($production->movementDetails as $detail)
                                @php
                                $precio = number_format($detail->product->productSede
                                ->where('headquarter_id', $production->headquarter_id)
                                ->first()->unit_price
                                ?? $detail->product->unit_price
                                , 2);
                                $cantidad = $detail->quantity;
                                @endphp
                                <tr>
                                    <td>{{ $production->headquarter->nombre }}</td>
                                    <td>{{ $detail->product->nombre }}</td>
                                    <td>{{ number_format($cantidad, 2) }}</td>
                                    <td>{{ number_format($precio, 2) }}</td>
                                    <td>{{ number_format($precio * $cantidad, 2) }}</td>
                                    <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                    <td>{{ $production->date }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-production-id="{{ $detail->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $productions->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta producción?</p>
                <p class="text-muted">Esta acción no se puede deshacer y afectará el stock del producto.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Script para generar PDF
    document.getElementById('btnPDF').addEventListener('click', function(e) {
        e.preventDefault();
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;

        // Construye la URL usando route() y reemplaza los parámetros
        let url = "{{ route('production.pdf', ['startDate' => 'START', 'endDate' => 'END', 'sede' => 'SEDE']) }}";
        url = url.replace('START', startDate || '{{ now()->startOfYear()->format("Y-m-d") }}');
        url = url.replace('END', endDate || '{{ now()->endOfYear()->format("Y-m-d") }}');
        url = url.replace('SEDE', sede);

        window.open(url, '_blank');
    });

    document.getElementById('btnPDFanticipos').addEventListener('click', function(e) {
        e.preventDefault();
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;

        // Construye la URL usando route() y reemplaza los parámetros
        let url = "{{ route('production.pdfanticipos', ['startDate' => 'START', 'endDate' => 'END', 'sede' => 'SEDE']) }}";
        url = url.replace('START', startDate || '{{ now()->startOfYear()->format("Y-m-d") }}');
        url = url.replace('END', endDate || '{{ now()->endOfYear()->format("Y-m-d") }}');
        url = url.replace('SEDE', sede);

        window.open(url, '_blank');
    });

    // Script para PDF Resumen
    document.getElementById('btnPDFResumen').addEventListener('click', function(e) {
        e.preventDefault();
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;
        const turno = document.querySelector('select[name="turno"]').value;

        let url = "{{ route('production.pdf-resumen', ['startDate' => 'START', 'endDate' => 'END', 'sede' => 'SEDE', 'turno' => 'TURNO']) }}";
        url = url.replace('START', startDate || '{{ now()->startOfYear()->format("Y-m-d") }}');
        url = url.replace('END', endDate || '{{ now()->endOfYear()->format("Y-m-d") }}');
        url = url.replace('SEDE', sede || '');
        url = url.replace('TURNO', turno || '');

        window.open(url, '_blank');
    });

    // Script para manejar el modal de eliminación
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteModal');
        const deleteForm = document.getElementById('deleteForm');

        // Escuchar cuando se abre el modal
        deleteModal.addEventListener('show.bs.modal', function(event) {
            // Botón que activó el modal
            const button = event.relatedTarget;
            // Obtener el ID de la producción
            const productionId = button.getAttribute('data-production-id');

            // Construir la URL usando la función route de Laravel
            const deleteUrl = "{{ route('production.destroy', ':id') }}".replace(':id', productionId);

            // Actualizar la acción del formulario
            deleteForm.action = deleteUrl;
        });
    });
</script>
@endsection