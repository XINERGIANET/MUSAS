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
<h2>Reporte de transformaciones</h2>
<p>Listado de transformaciones</p>
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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Turno</label>
                                    <select class="form-control" name="shift">
                                        <option value="">Seleccione un turno</option>
                                        <option value="0" {{ request('shift') === '0' ? 'selected' : '' }}>Mañana</option>
                                        <option value="1" {{ request('shift') === '1' ? 'selected' : '' }}>Tarde</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <div class="mb-3 w-50s me-2">
                                    <a href="{{ route('transformations_report.index') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>   
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <a href="{{ route('wasteReport.pdf',
                                    [
                                        'startDate' => request()->start_date ?? now()->startOfYear()->format('Y-m-d'),
                                        'endDate' => request()->end_date ?? now()->endOfYear()->format('Y-m-d')
                                    ]) }}"
                                    class="mb-3 btn btn-info" type="button" id="btnPDF">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="purchaseTable">
                                <thead class="table">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movements as $movement)
                                    @foreach ($movement->movementDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->product ? $detail->product->nombre : 'Sin producto' }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>{{ $movement->date }}</td>
                                    </tr>
                                    @if ($detail->transformado == 1)
                                    @php
                                    $porciones = [['nombre' => 'Torta Chocolate', 'cantidad' => $detail->quantity]];
                                    @endphp
                                    @endif
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $movements->links('pagination::bootstrap-4') }}
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
<script>
    /* var products = @json($products);
    $('#search-product').on('input', function() {
        $('#producto_id').val('');
    });

    document.getElementById('clearForm').addEventListener('click', function () {
        document.getElementById('producto_id').value = '';
        document.getElementById('search-product').value = '';
        document.getElementById('fecha').value = '';

        document.getElementById('createWasteForm').submit();
    });

    $('#search-product').autocomplete({
        source: function(request, response) {
            // Filtra los productos localmente
            var results = $.map(products, function(item) {
                if (item.nombre.toLowerCase().includes(request.term.toLowerCase())) {
                    return {
                        label: item.nombre,
                        value: item.nombre,
                        id: item.id
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
    }; */

    // $('#createWasteForm').on('submit', function(e) {
    //     e.preventDefault();
    //     var formData = $(this).serialize();
    //     $.ajax({
    //         url: $(this).attr('action'),
    //         method: $(this).attr('method'),
    //         data: formData,
    //         success: function(response) {
    //             if (response.status) {
    //               ToastMessage.fire({ text: 'Registro guardado' }).then(() => location.reload());
    //               $('#createWasteForm')[0].reset();
    //             } else {
    //                 alert('Error: ' + response.error);
    //             }
    //         },
    //         error: function(xhr) {
    //           ToastError.fire({ text: 'Ocurrió un error' });
    //         }
    //     });
    // });
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('fromFilter');
    const buttonFiltrar = document.getElementById('btnFiltrar');
    const spinner = document.getElementById('global-spinner');

    let clickedFiltrar = false;

    spinner.classList.remove('spinner-visible');
    spinner.classList.add('spinner-hidden');

    buttonFiltrar.addEventListener('click', function () {
        clickedFiltrar = true;
    });

    form.addEventListener('submit', function () {
        if (clickedFiltrar) {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        }

        clickedFiltrar = false;
    });
});
</script>
@endsection