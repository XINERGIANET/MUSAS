@extends('template.index')

@section('header')
<h2>Histórico de pagos</h2>
<p>Lista total de pagos</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="" id="formFilter">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request()->start_date ? request()->start_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request()->end_date ? request()->end_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Método de pago</label>
                                    <select name="payment_method_id" class="form-control">
                                        <option value="">Seleccione un método de pago</option>
                                        @foreach ($methods as $method)
                                        <option value="{{ $method->id }}" {{ request()->payment_method_id == $method->id ? 'selected' : '' }}>{{ $method->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Sedes</label>
                                    <select name="headquarter_id" class="form-control">
                                        <option value="">Seleccione una sede</option>
                                        <option value="sin_sede" {{ request()->headquarter_id == 'sin_sede' ? 'selected' : '' }}>Sin sede</option>
                                        @foreach ($headquarters as $headquarter)
                                        <option value="{{ $headquarter->id }}" {{ request()->headquarter_id == $headquarter->id ? 'selected' : '' }}>{{ $headquarter->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Usuarios</label>
                                    <select name="user_id" class="form-control">
                                        <option value="">Seleccione un usuario</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ request()->user_id == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col d-flex align-items-end mb-3">
                                <div class=" w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <!-- <div class=" w-50s me-2">
                                     <button type="button" class="btn btn-danger w-100" id="btnPDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF general
                                    </button>
                                </div> -->
                                <div class=" w-50s me-2">
                                     <button type="button" class="btn btn-danger w-100" id="btnPDFAgrupado">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF por método
                                    </button>
                                </div>
                                <div class=" w-50s me-2">
                                    <a href="{{ route('payment.index') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 mt-4 mr-2">
                    <div class="d-flex justify-content-end">
                        <div>
                            <h5>
                                <strong>Total: S/ {{ number_format($total, 2, '.', ',') }}</strong>
                            </h5>
                        </div>
                    </div>
                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mét. de pago</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Turno</th>
                                    <th>Usuario</th>
                                    <th>Sede</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->paymentMethod->nombre }}</td>
                                    <td>{{ $payment->fecha->format('d/m/Y') }}</td>
                                    <td>{{ number_format($payment->monto,2) }}</td>
                                    <td>{{ $payment->turno === 0 ? 'Mañana' : ( $payment->turno === 1 ? 'Tarde' : '-') }}</td>
                                    <td>{{ $payment->usuario ? $payment->usuario->email : '-' }}</td>
                                    <td>{{ $payment->headquarter ? $payment->headquarter->nombre : 'Sin sede' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $payments->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
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

<script>


    document.addEventListener('DOMContentLoaded', function() {

        const spinner = document.getElementById('global-spinner');

    });

    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFilter');
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


<script>

    // document.getElementById('btnPDF').addEventListener('click', function() {
    //     const form = document.getElementById('formFilter');
    //     const formData = new FormData(form);

    //     // Construir la query string con todos los campos del formulario
    //     const params = new URLSearchParams(formData).toString();

    //     // Ruta a la que quieres enviar los datos (ajusta según tu ruta)
    //     const url = '{{ route("payment.pdf") }}' + '?' + params;

    //     // Redirigir para descargar el PDF (GET)
    //     window.open(url, '_blank');

    // });

     document.getElementById('btnPDFAgrupado').addEventListener('click', function() {
        const form = document.getElementById('formFilter');
        const formData = new FormData(form);

        // Construir la query string con todos los campos del formulario
        const params = new URLSearchParams(formData).toString();

        // Ruta a la que quieres enviar los datos (ajusta según tu ruta)
        const url = '{{ route("payment.pdfAgrupado") }}' + '?' + params;

        // Redirigir para descargar el PDF (GET)
        window.open(url, '_blank');

    });

</script>

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