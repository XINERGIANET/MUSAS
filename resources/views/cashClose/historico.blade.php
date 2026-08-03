@extends('template.index')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css') }}" />
@endsection

@section('nav')
@if(auth()->user()->hasRole('admin'))
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary" href="{{ route('payment.cashClose') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary active" href="{{ route('cashClose.historico') }}">Histórico</a>
    </li>
</ul>
@endif
@endsection

@section('header')
<h2>Histórico de Cierre de Caja</h2>
<p>Lista de Cierre de Caja</p>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                {{-- Filtros --}}
                <form class="mb-4" id="date-form" method="GET" action="{{ route('cashClose.historico') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="date" id="date"
                                value="{{ request()->date ?? now()->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Turno</label>
                            <select class="form-select" name="turno" id="turno">
                                <option value="">Todos</option>
                                <option value="0" {{ request()->turno == 0 ? 'selected' : '' }}>Mañana</option>
                                <option value="1" {{ request()->turno == 1 ? 'selected' : '' }}>Tarde</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Sede</label>
                            <select class="form-select" name="headquarter_id" id="headquarter_id">
                                <option value="">Sin Sede</option>
                                @foreach($sedes as $hq)
                                <option value="{{ $hq->id }}" {{ (string)request()->headquarter_id === (string)$hq->id ? 'selected' : '' }}>
                                    {{ $hq->nombre }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Usuario</label>
                            <select class="form-select" name="user_id" id="user_id">
                                <option value="">Todos</option>
                                @foreach($todosLosUsuarios as $u)
                                <option
                                    value="{{ $u->id }}"
                                    {{ (string)request()->user_id === (string)$u->id ? 'selected' : '' }}>
                                    {{ $u->nombre }} @if(is_null($u->sede_id)) @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('cashClose.historico', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-warning ms-2">Hoy</a>
                        </div>
                    </div>

                </form>

                <label for="monto" class="form-label">Monto: </label>
                <div class="input-group mb-3" style="width: 21rem;">
                    <input type="number" class="form-control form-control" placeholder="Ingrese un monto" name="monto" id="monto" value="{{ $monto ?? '' }}" step="0.01">
                </div>

                <label class="d-block my-4">Turno: {{ (int)request()->turno === 0 ? 'Mañana' : 'Tarde' }}</label>

                {{-- Tabla de resultados (mismo formato que mostraste) --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm w-auto">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">Ventas</th>
                                <th rowspan="2" class="text-center">Correcciones</th>
                            </tr>
                            <tr>
                                <th>Método de pago</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- VENTAS DIRECTAS (no se renderizan si es delivery) --}}
                            {{-- @if (!auth()->user()->hasRole('delivery')) --}}
                            @foreach ($ventas_payment_methods as $payment_method)
                            <tr>
                                <td>Ventas | {{ ucfirst(strtolower($payment_method->nombre)) }}</td>
                                <td align="right">{{ number_format($payment_method->total, 2) }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td><strong>TOTAL VENTAS DIRECTAS</strong></td>
                                <td align="right"><strong>{{ number_format($total_ventas, 2) }}</strong></td>
                                <td></td>
                            </tr>
                            {{-- @endif --}}

                            {{-- ANTICIPADAS: PAGO INICIAL --}}
                            @foreach ($anticipadas_inicial_payment_methods as $payment_method)
                            <tr>
                                <td>Anticipos | {{ ucfirst(strtolower($payment_method->nombre)) }}</td>
                                <td align="right">{{ number_format($payment_method->total, 2) }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td><strong>TOTAL ANTICIPOS</strong></td>
                                <td align="right"><strong>{{ number_format($total_anticipadas_iniciales, 2) }}</strong></td>
                                <td></td>
                            </tr>

                            {{-- ANTICIPADAS: SALDOS --}}
                            @foreach ($anticipadas_pendiente_payment_methods as $payment_method)
                            <tr>
                                <td>Saldos | {{ ucfirst(strtolower($payment_method->nombre)) }}</td>
                                <td align="right">{{ number_format($payment_method->total, 2) }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td><strong>TOTAL SALDOS</strong></td>
                                <td align="right"><strong>{{ number_format($total_anticipadas_pendientes, 2) }}</strong></td>
                                <td></td>
                            </tr>

                            @php
                            // Consolidado por método: Ventas + Inicial + Pendientes
                            $totales_por_metodo = [];

                            // Ventas directas (si aplica)
                            if (!auth()->user()->hasRole('delivery')) {
                                foreach ($ventas_payment_methods as $pm) {
                                    $nombre = ucfirst(strtolower($pm->nombre));
                                    if (!isset($totales_por_metodo[$nombre])) {
                                        $totales_por_metodo[$nombre] = ['total' => 0, 'id' => $pm->id];
                                    }
                                    $totales_por_metodo[$nombre]['total'] += $pm->total;
                                }
                            }
                            // Anticipos
                            foreach ($anticipadas_inicial_payment_methods as $pm) {
                                $nombre = ucfirst(strtolower($pm->nombre));
                                if (!isset($totales_por_metodo[$nombre])) {
                                    $totales_por_metodo[$nombre] = ['total' => 0, 'id' => $pm->id];
                                }
                                $totales_por_metodo[$nombre]['total'] += $pm->total;
                            }
                            // Saldos
                            foreach ($anticipadas_pendiente_payment_methods as $pm) {
                                $nombre = ucfirst(strtolower($pm->nombre));
                                if (!isset($totales_por_metodo[$nombre])) {
                                    $totales_por_metodo[$nombre] = ['total' => 0, 'id' => $pm->id];
                                }
                                $totales_por_metodo[$nombre]['total'] += $pm->total;
                            }

                            // Gran total
                            $gran_total = array_sum($totales_por_metodo);
                            @endphp

                            {{-- Mostrar consolidado por método --}}
                            @foreach ($totales_por_metodo as $nombre => $data)
                            <tr>
                                <td>{{ $nombre }}</td>
                                <td align="right">{{ number_format($data['total'], 2) }}</td>
                               <td>
                                    <input type="text"
                                        name="balance[{{ $data['id'] }}]"
                                        class="form-control border-dark"
                                        value="{{ $arqueos->where('payment_method_id', $data['id'])->first() ? $arqueos->where('payment_method_id', $data['id'])->first()->monto : '' }}">
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td><strong>TOTAL</strong></td>
                                <td align="right"><strong>{{ number_format($gran_total, 2) }}</strong></td>
                            </tr>

                            <tr>
                                <td class="fw-bold" colspan="2">Efectivo = Ventas + Anticipadas - Egresos</td>
                            </tr>
                            <tr>
                                <td class="fw-bold" colspan="2">Efectivo = {{ number_format($saldo, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary" id="openArqueoModal">Guardar arqueo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmArqueoModal" tabindex="-1" aria-labelledby="confirmArqueoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmArqueoLabel">¿Está seguro de guardar el arqueo?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalArqueoBody">
        <!-- Aquí se mostrarán los datos -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="confirmArqueoBtn">Confirmar</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/hope-ui.min.js') }}"></script>

<script>
    document.getElementById('date-form').addEventListener('submit', function(e) {
        const dateInput = document.getElementById('date');
        if (!dateInput.value) {
            const today = new Date().toISOString().split('T')[0]; // formato YYYY-MM-DD
            dateInput.value = today;
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('date-form');
        const sedeSel = document.getElementById('headquarter_id');
        const userSel = document.getElementById('user_id');
        const dateIn = document.getElementById('date');

        // Asegurar que siempre se envía fecha (si el usuario la borra)
        form.addEventListener('submit', function() {
            if (!dateIn.value) dateIn.value = new Date().toISOString().split('T')[0];
        });

    document.getElementById('openArqueoModal').addEventListener('click', function() {
        // Obtener los datos de los inputs
        const fecha = document.getElementById('date').value;
        const turno = document.getElementById('turno').value;
        const sede = document.getElementById('headquarter_id').selectedOptions[0].text;
        const usuario = document.getElementById('user_id').selectedOptions[0].text;
        const monto = document.getElementById('monto').value;

        // Obtener los balances
        let balances = [];
        let balancesData = [];
        document.querySelectorAll('input[name^="balance"]').forEach(function(input) {
            let nombre = input.closest('tr').querySelector('td').innerText.trim();
            let paymentMethodId = input.name.match(/\d+/) ? input.name.match(/\d+/)[0] : null;
            balances.push(`${nombre}: ${input.value}`);
            balancesData.push({
                payment_method_id: paymentMethodId,
                value: input.value
            });
        });

        // Construir el HTML
        let html = `
            <strong>Fecha:</strong> ${fecha}<br>
            <strong>Turno:</strong> ${turno === "0" ? "Mañana" : turno === "1" ? "Tarde" : "Todos"}<br>
            <strong>Sede:</strong> ${sede}<br>
            <strong>Usuario:</strong> ${usuario}<br>
            <strong>Balances:</strong><br>
            <ul>${balances.map(b => `<li>${b}</li>`).join('')}</ul>
        `;
        document.getElementById('modalArqueoBody').innerHTML = html;

        // Mostrar el modal (Bootstrap 5)
        let modal = new bootstrap.Modal(document.getElementById('confirmArqueoModal'));
        modal.show();

        console.log({
            fecha,
            turno,
            sede,
            usuario,
            balancesData
        });

    });

    document.getElementById('confirmArqueoBtn').onclick = function() {
        const fecha = document.getElementById('date').value;
        const turno = document.getElementById('turno').value;
        const sede = document.getElementById('headquarter_id').value;
        const usuario = document.getElementById('user_id').value;
        const monto = document.getElementById('monto').value;

        let balancesData = [];
        document.querySelectorAll('input[name^="balance"]').forEach(function(input) {
            let paymentMethodId = input.name.match(/\d+/) ? input.name.match(/\d+/)[0] : null;
            balancesData.push({
                payment_method_id: paymentMethodId,
                value: input.value
            });
        });

        $.ajax({
            url: "{{ route('arqueo.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                fecha: fecha,
                turno: turno,
                sede: sede,
                usuario: usuario,
                monto: monto,
                balances: balancesData
            },
            success: function(response) {
                if (response.success) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Arqueo guardado correctamente'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    ToastError.fire({
                        text: response.message || 'Error al guardar arqueo'
                    });
                }
            },
            error: function(xhr) {
                let msg = 'Error inesperado';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                ToastError.fire({
                    text: msg
                });
            }
        });
    };

        // function filtrarUsuariosPorSede() {
        //     const sede = sedeSel.value; // '' = no seleccionada
        //     const prev = userSel.value; // para intentar mantener selección

        //     // Recorremos todas menos la primera ("Todos")
        //     for (let i = 1; i < userSel.options.length; i++) {
        //         const opt = userSel.options[i];
        //         const userSede = opt.dataset.sede || '';
        //         const rolId = parseInt(opt.dataset.rol || '0', 10);

        //         let visible;

        //         if (sede === '') {
        //             visible = (rolId === 5);
        //         } else {
        //             visible = (userSede === sede || userSede === '') && rolId !== 5;
        //         }

        //         opt.hidden = !visible;
        //         opt.disabled = !visible;
        //     }

        //     if (userSel.selectedOptions[0] && (userSel.selectedOptions[0].hidden || userSel.selectedOptions[0].disabled)) {
        //         userSel.value = '';
        //     } else {
        //         userSel.value = prev;
        //     }
        // }

        // Filtrar al cargar (en caso venga sede desde el query) y al cambiar
        // filtrarUsuariosPorSede();
        // sedeSel.addEventListener('change', filtrarUsuariosPorSede);
    });
</script>
@endsection