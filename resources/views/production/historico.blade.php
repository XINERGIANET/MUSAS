@extends('template.index')

@php
$destroyRoute = Route::currentRouteName() === 'production.historico'
? route('production.destroyPersonalized', ':id')
: (Route::currentRouteName() === 'production.historicoDelivery'
? route('production.destroyDelivery', ':id')
: route('production.destroyPersonalized', ':id')); // valor por defecto
$registerRoute = Route::currentRouteName() === 'production.historico'
? route('production.personalized')
: (Route::currentRouteName() === 'production.historicoDelivery'
? route('production.delivery')
: route('production.personalized')); // valor por defecto
$limpiarRoute = Route::currentRouteName() === 'production.historico'
    ? route('production.historico')
    : (Route::currentRouteName() === 'production.historicoDelivery'
        ? route('production.historicoDelivery')
        : route('production.historico')); // valor por defecto
@endphp

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary" href="{{ $registerRoute }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary active" href="{{ url()->current() }}">Historico</a>
    </li>
</ul>
@endsection

@section('styles')
<style>
    #btnImportar {
        margin: 0 10px !important;
    }
</style>
@endsection

@section('header')
<h2>{{ $title }}</h2>
<p>Histórico de producción</p>
@endsection


@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <div class="col-sm-12 mb-3">
                            <form method="GET" action="{{ url()->current() }}">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="start_date" class="form-label">Fecha Inicio</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="end_date" class="form-label">Fecha Fin</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="headquarter_id" class="form-label">Sede</label>
                                        <select class="form-control" id="headquarter_id" name="headquarter_id">
                                            <option value="">Todas</option>
                                            @foreach ($headquarters as $headquarter)
                                            <option value="{{ $headquarter->id }}" {{ request('headquarter_id') == $headquarter->id ? 'selected' : '' }}>
                                                {{ $headquarter->nombre }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="turno" class="form-label">Turno</label>
                                        <select class="form-control" id="turno" name="turno">
                                            <option value="">Todos</option>
                                            <option value="0" {{ request('turno') === '0' ? 'selected' : '' }}>Mañana</option>
                                            <option value="1" {{ request('turno') === '1' ? 'selected' : '' }}>Tarde</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="staff_id" class="form-label">Encargado</label>
                                        <select class="form-control" id="staff_id" name="staff_id">
                                            <option value="">Todos</option>
                                            @foreach ($encargados as $encargado)
                                            <option value="{{ $encargado->id }}" {{ request('staff_id') == $encargado->id ? 'selected' : '' }}>
                                                {{ $encargado->nombre }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-4 align-items-center">
                                    <div class="col-md-6 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Filtrar</button>
                                        <!-- <a href="#" class="btn btn-info" id="btnPDF">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        <a href="#" class="btn btn-warning" id="btnPDFResumen">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF RESUMEN
                                        </a> -->
                                        <div class="w-50s me-2">
                                            <a href="{{ $limpiarRoute }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                <i class="fas fa-file-pdf"></i> INFORMES PDF
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item btn-pdf" href="#" id="btnPDF">
                                                        <i class="bi bi-file-earmark-text"></i> TOTAL PRODUCCIÓN 
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item btn-pdf-general" href="#" id="btnPDFResumen">
                                                        <i class="bi bi-file-earmark-text"></i> RESUMEN PRODUCCIÓN
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-md-6 text-end">
                                        <strong>Total: S/
                                            @php
                                            $total = 0;
                                            foreach($productions as $production) {
                                            foreach($production->movementDetails as $detail) {
                                            $total += $detail->quantity * $detail->unit_price;
                                            }
                                            }
                                            echo number_format($total, 2);
                                            @endphp
                                        </strong>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tabla de productos agregados -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="productionTable">
                                <thead class="table">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Producto</th>
                                        <th>Turno</th>
                                        <th>Sede</th>
                                        <th>Encargado</th>
                                        <th>Precio Unitario</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Fecha</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productions as $production)
                                    @foreach($production->movementDetails as $detail)
                                    <tr>
                                        <td>Personalizada</td>
                                        <td>{{ $detail->product->nombre }}</td>
                                        <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                        <td>{{ $production->headquarter->nombre }}</td>
                                        <td>{{ $detail->staff ? $detail->staff->nombre : 'Sin encargado' }}</td>
                                        <td class="text-end">S/ {{ number_format($detail->unit_price, 2) }}</td>
                                        <td class="text-end">{{ $detail->quantity }}</td>
                                        <td class="text-end">S/ {{ number_format($detail->quantity * $detail->unit_price, 2) }}</td>
                                        <td><small class="text-muted">{{ $production->created_at->format('d/m/Y H:i') }}</small></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="openDeleteModal({{ $production->id }}, '{{ $detail->product->nombre }}')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No hay producciones para mostrar</td>
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
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta producción?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


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

    .cantidad-input {
        width: 100px;
    }

    /* Limita la altura del menú y añade scroll vertical */
    .ui-autocomplete {
        max-height: 200px;
        /* ajusta la altura a tu gusto */
        overflow-y: auto;
        /* habilita scroll vertical */
        overflow-x: hidden;
        /* evita scroll horizontal */
        /* opcional: para que no tape otros elementos */
        z-index: 1000;
    }

    /* Opcional: mejorar visibilidad de cada ítem */
    .ui-menu-item-wrapper {
        white-space: nowrap;
        padding: 4px 8px;
    }
</style>
@endsection
@section('scripts')
<script>
    @php
    use Carbon\ Carbon;

    $routeName = Route::currentRouteName();
    $tipo = $routeName === 'production.historicoDelivery' ? 9 : 8;

    // Obtener inicio y fin del mes actual
    $defaultStart = now()-> startOfMonth()-> format('Y-m-d');
    $defaultEnd = now()-> endOfMonth()-> format('Y-m-d');

    $pdfRoute = route('production.pdf_personalized', [
        'startDate' => 'START',
        'endDate' => 'END',
        'sede' => 'SEDE',
        'turno' => 'TURNO',
        'tipo' => $tipo
    ]);

    $pdfResumenRoute = route('production.pdf_summary', [
        'startDate' => 'START',
        'endDate' => 'END',
        'sede' => 'SEDE',
        'turno' => 'TURNO',
        'tipo' => $tipo
    ]);
    @endphp

    document.getElementById('btnPDF').addEventListener('click', function(e) {
        e.preventDefault();

        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;
        const turno = document.querySelector('select[name="turno"]').value;

        let url = "{{ $pdfRoute }}";

        // Fechas por defecto del mes actual
        const defaultStart = "{{ $defaultStart }}";
        const defaultEnd = "{{ $defaultEnd }}";

        // Reemplazar en la URL
        url = url.replace('START', startDate || defaultStart);
        url = url.replace('END', endDate || defaultEnd);
        url = url.replace('SEDE', sede || 'null');
        url = url.replace('TURNO', turno || 'null');

        window.open(url, '_blank');
    });

    document.getElementById('btnPDFResumen').addEventListener('click', function(e) {
        e.preventDefault();

        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]').value;
        const turno = document.querySelector('select[name="turno"]').value;

        let url = "{{ $pdfResumenRoute }}";

        // Fechas por defecto del mes actual
        const defaultStart = "{{ $defaultStart }}";
        const defaultEnd = "{{ $defaultEnd }}";

        // Reemplazar en la URL
        url = url.replace('START', startDate || defaultStart);
        url = url.replace('END', endDate || defaultEnd);
        url = url.replace('SEDE', sede || 'null');
        url = url.replace('TURNO', turno || 'null');

        window.open(url, '_blank');
    });

    let currentProductionId = null;

    function openDeleteModal(productionId, productName) {
        currentProductionId = productionId;
        // Mostrar el modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    function confirmDelete() {
        if (currentProductionId) {
            // Configurar el formulario con la acción correcta
            const form = document.getElementById('deleteForm');
            form.action = "{{ $destroyRoute }}".replace(':id', currentProductionId);

            // Enviar el formulario
            form.submit();
        }
    }

    // Manejar el cierre del modal
    document.getElementById('deleteModal').addEventListener('hidden.bs.modal', function() {
        currentProductionId = null;
        document.getElementById('productName').textContent = '';
    });
</script>
@endsection