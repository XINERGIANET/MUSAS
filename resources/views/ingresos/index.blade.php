@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary" href="{{ route('ingresos.create') }}?categoria={{ request('categoria') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary active" href="{{ route('ingresos.index') }}?categoria={{ request('categoria') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Historial de Ingresos</h2>
<p>Registros de ingresos realizados</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('ingresos.index', ['categoria' => $categoria]) }}">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Sede</label>
                                    <select class="form-select" id="headquarter_id" name="headquarter_id">
                                        <option value="">Seleccione una sede</option>
                                        @foreach ($sedes as $s)
                                            <option value="{{ $s->id }}" {{ request('headquarter_id') == $s->id ? 'selected' : '' }}>
                                                {{ $s->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="categoria" value="{{ $categoria }}"/>

                            <div class="col-md-3 d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                                <div class="mb-3 w-50s me-2">
                                    <a href="{{ route('ingresos.index', ['categoria' => $categoria]) }}"
                                    class="btn btn-warning w-100 text-nowrap" id="btnLimpiar">
                                        Limpiar
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-3 d-flex align-items-end flex-nowrap gap-2">
                                <button class="btn btn-warning d-inline-flex align-items-center text-nowrap" type="button" id="btnPDF_resumen">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF RESUMEN
                                </button>

                                <a href="{{ route('ingresos.pdf', [
                                    'startDate' => request('start_date') ?? now()->startOfYear()->format('Y-m-d'),
                                    'endDate'   => request('end_date')   ?? now()->endOfYear()->format('Y-m-d'),
                                    'categoria' => $categoria, // <-- usa la categoría activa
                                ]) }}"
                                class="btn btn-info d-inline-flex align-items-center text-nowrap"
                                id="btnPDF">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </a>
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productions as $production)
                                    @foreach($production->movementDetails as $detail)
                                        <tr>
                                            <td>{{ $production->headquarter->nombre }}</td>
                                            <td>{{ $detail->product->nombre }}</td>
                                            <td>{{ number_format($detail->quantity, 2) }}</td>
                                            <td>{{ number_format($detail->product->unit_price, 2) }}</td>
                                            <td>{{ number_format($detail->product->unit_price * $detail->quantity, 2) }}</td>
                                            <td>{{ $production->turno == 0 ? 'Mañana' : 'Tarde' }}</td>
                                            <td>{{ $production->date }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $productions->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnPDF_resumen').addEventListener('click', function () {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const sede = document.querySelector('select[name="headquarter_id"]')?.value;

        // Leer la categoría desde la URL
        const paramsUrl = new URLSearchParams(window.location.search);
        const categoria = paramsUrl.get('categoria');

        let pdfUrl = '{{ route("ingresos.pdf-resumen") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (categoria) params.append('categoria', categoria);
        if (sede) params.append('headquarter_id', sede);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        // Descargar el PDF
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'resumen_ingresos.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>
@endsection