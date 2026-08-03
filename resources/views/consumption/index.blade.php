@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary" href="{{ route('consumption.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary active" href="{{ route('consumption.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Historial de Consumos</h2>
<p>Lista de salidas</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="{{ route('consumption.index') }}" method="GET" class="mt-3" id="consumptionFilterForm">
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
                                    <label class="form-label">Buscar Mat. Prima</label>
                                    <input type="text" class="form-control" name="search" value="{{ request()->search ?? '' }}" placeholder="Buscar Mat. Prima">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Encargado</label>
                                    <select class="form-control" name="staff_search">
                                        <option value="">Seleccionar Encargado</option>
                                        @foreach($staff as $user)
                                            <option value="{{ $user->id }}" {{ request()->staff_search == $user->id ? 'selected' : '' }}>
                                                {{ $user->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Área</label>
                                    <select name="area" id="area" class="form-select">
                                        <option value="">Seleccionar Área</option>
                                        <option value="panaderia" {{ request('area') == 'panaderia' ? 'selected' : '' }}>Panadería</option>
                                        <option value="pasteleria" {{ request('area') == 'pasteleria' ? 'selected' : '' }}>Pastelería</option>
                                        <option value="cocina" {{ request('area') == 'cocina' ? 'selected' : '' }}>Cocina</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <div class="me-2">
                                    <a class="mb-3 nav-link btn btn-success active" href="{{ route('consumption.index') }}">Limpiar Filtros</a>
                                </div>
                                <button class="mb-3 me-2 btn btn-info" type="button" id="btnPDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                                <button class="mb-3 me-2 btn btn-warning" type="button" id="btnPDFResumen">
                                    <i class="bi bi-file-earmark-text"></i> PDF Resumen
                                </button>
                                <button class="mb-3 me-2 btn btn-danger" type="button" id="btnPDFArea">
                                    <i class="bi bi-file-earmark-text"></i> PDF Area
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <div class="row d-flex mt-3">
                            <div class="d-flex justify-content-end align-items-center mb-3">
                                <h5>
                                    <strong>TOTAL S/ {{ number_format($total, 2, '.', '') }}</strong>
                                </h5>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Materia Prima</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                        <!-- <th>Merma</th> -->
                                        <th>Encargado</th>
                                        <th>Área</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($consumptions as $consumption)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($consumption->date)->format('d/m/Y') }}</td>
                                        <td>
                                            @if($consumption->product)
                                            {{ $consumption->product->nombre }}
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                        <td>{{ $consumption->quantity }}</td>
                                        <td>{{ number_format($consumption->product->unit_price,2) }}</td>
                                        <td>{{ number_format($consumption->quantity * $consumption->product->unit_price,2) }}</td>
                                        <td>{{ $consumption->staff->nombre }}</td>
                                        <td>
                                        @php
                                            $area = ucfirst($consumption->area);
                                            $area = str_replace('pasteleria', 'Pastelería', $area);
                                            $area = str_replace('panaderia', 'Panadería', $area);
                                            $area = str_replace('cocina', 'Cocina', $area);
                                        @endphp
                                        {{ $area }}
                                        </td>
                                        <td>
                                           <button class="btn btn-warning btn-sm btn-icon btn-edit"
                                                data-id="{{ $consumption->id }}"
                                                data-staff="{{ $consumption->staff_id }}"
                                                data-quantity="{{ $consumption->quantity }}"
                                                data-area="{{ $consumption->area }}"
                                                title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-icon btn-eliminar"
                                                data-id="{{ $consumption->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#eliminarModal"
                                                title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <!-- <td>{{ $consumption->merma == 1 ? 'Sí' : 'No' }}</td> -->
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $consumptions->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Editar consumo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="id" id="editId">

          <div class="mb-3">
            <label for="editStaff" class="form-label">Encargado</label>
            <select class="form-select" name="staff_id" id="editStaff" required>
              @foreach($staff as $persona)
                <option value="{{ $persona->id }}">{{ $persona->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="editQuantity" class="form-label">Cantidad</label>
            <input type="number" class="form-control" name="quantity" id="editQuantity" step="0.01" min="0.01" required>
          </div>

          <div class="mb-3">
            <label for="editArea" class="form-label">Área</label>
            <select class="form-select" name="area" id="editArea" required>
                <option value="">Seleccionar área</option>
                <option value="panaderia">Panadería</option>
                <option value="pasteleria">Pastelería</option>
                <option value="cocina">Cocina</option>
            </select>
            </div>


        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEliminar" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eliminarModalLabel">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de eliminar este consumo?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmEliminarBtn">Eliminar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 9999;">
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.btn-edit');
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const spinner = document.getElementById('global-spinner');
        const formEditar = document.getElementById('editForm');

        // Mostrar datos en el modal al hacer clic en "Editar"
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const staff = button.dataset.staff;
                const quantity = button.dataset.quantity;
                const area = button.dataset.area;

                // Rellenar el formulario
                document.getElementById('editId').value = id;
                document.getElementById('editStaff').value = staff;
                document.getElementById('editQuantity').value = quantity;
                document.getElementById('editArea').value = area;

                // Cambiar la acción del formulario
                formEditar.action = `{{ url('consumption') }}/${id}`;

                editModal.show();
            });
        });

        // Mostrar spinner al enviar el formulario
        formEditar.addEventListener('submit', function () {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const eliminarButtons = document.querySelectorAll('.btn-eliminar');
        const formEliminar = document.getElementById('formEliminar');
        const spinner = document.getElementById('global-spinner');
        const confirmBtn = document.getElementById('confirmEliminarBtn');

        eliminarButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                formEliminar.action = `{{ url('consumption') }}/${id}`;
            });
        });

        formEliminar.addEventListener('submit', function () {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('consumptionFilterForm');
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

    $(document).ready(function() {
        $('#checkMerma').change(function() {
            let merma = $(this).is(':checked') ? 1 : null;

            if (merma !== null) {
                window.location.href = '{{ route('consumption.index') }}' + '?merma=' + merma;
            } else {
                window.location.href = '{{ route('consumption.index') }}';
            }
        });
    });

    // Script para el botón PDF normal - igual que en compras
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnPDF').addEventListener('click', function() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const search = document.querySelector('input[name="search"]').value;
            const staffSearch = document.querySelector('select[name="staff_search"]').value;
            const merma = document.querySelector('input[name="merma"]:checked') ? 1 : 0;

            // Usar la ruta del PDF normal
            let pdfUrl = '{{ route("consumption.pdf") }}';
            const params = new URLSearchParams();

            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (search) params.append('search', search);
            if (staffSearch) params.append('staff_search', staffSearch);
            if (merma) params.append('merma', merma);

            if (params.toString()) {
                pdfUrl += '?' + params.toString();
            }

            console.log('URL generada para PDF:', pdfUrl);

            // Crear un enlace temporal para forzar la descarga
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = 'reporte_consumos.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    // Script para el botón PDF Resumen - igual que en compras
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnPDFResumen').addEventListener('click', function() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const search = document.querySelector('input[name="search"]').value;
            const staffSearch = document.querySelector('select[name="staff_search"]').value;
            const merma = document.querySelector('input[name="merma"]:checked') ? 1 : 0;

            // Usar la ruta del PDF resumen
            let pdfUrl = '{{ route("consumption.pdf-resumen") }}';
            const params = new URLSearchParams();

            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (search) params.append('search', search);
            if (staffSearch) params.append('staff_search', staffSearch);
            if (merma) params.append('merma', merma);

            if (params.toString()) {
                pdfUrl += '?' + params.toString();
            }

            console.log('URL generada para PDF Resumen:', pdfUrl);

            // Crear un enlace temporal para forzar la descarga
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = 'resumen_consumos.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    // Script para el botón PDF Area
    document.addEventListener('DOMContentLoaded', function() {
    const btnArea = document.getElementById('btnPDFArea');
    if (btnArea) {
        btnArea.addEventListener('click', function() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const search = document.querySelector('input[name="search"]').value;
            const staffSearch = document.querySelector('select[name="staff_search"]').value;
            const area = document.querySelector('select[name="area"]').value;

            let pdfUrl = '{{ route("consumption.pdf-areas") }}';
            const params = new URLSearchParams();

            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (search) params.append('search', search);
            if (staffSearch) params.append('staff_search', staffSearch);
            if (area) params.append('area', area);

            if (params.toString()) {
                pdfUrl += '?' + params.toString();
            }

            // Crear un enlace temporal para forzar la descarga
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = 'area_consumos.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
</script>
@endsection