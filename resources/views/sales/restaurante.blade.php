@extends('template.index')

@section('nav')
<style>
    .card-mesa.borde-naranja {
        border: 6px solid #ffa500 !important;
    }

    .card-mesa.borde-rojo {
        border: 6px solid red !important;
    }

    .card-mesa.borde-verde {
        border: 6px solid green !important;
    }
</style>

<x-nav-sales />

@endsection

@section('header')
<h2>Punto de Venta Restaurante</h2>
<p>Lista de mesas</p>
@endsection

@section('content')
@php
$colors = ['btn-outline-primary', 'btn-outline-success', 'btn-outline-info', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-dark'];
@endphp
<div class="container-fluid content-inner mt-n5 py-0">
    <!-- Card que contiene el formulario y la tabla -->
    <div class="card shadow">
        <!-- Cuerpo del Card -->
        <div class="card-body">
            <div class="row g-4">
                @foreach($mesas as $mesa)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 rounded-4 text-center h-100 card-mesa" id="mesa-card-{{ $mesa->id }}" data-mesa-id="{{ $mesa->id }}" data-opened-at="{{ $mesa->opened_at }}">
                        <div class="card-body d-flex flex-column justify-content-between ">
                            <h5 class="card-title mb-3 fw-bold">{{ $mesa->name }}</h5>

                            <span id="estado-mesa-{{ $mesa->id }}" class="badge mb-2 {{ $mesa->status == 'libre' ? 'bg-success' : 'bg-danger' }} fs-5">
                                {{ ucfirst($mesa->status) }}
                            </span>

                            <div id="acciones-mesa-{{ $mesa->id }}">
                                @if($mesa->status === 'libre')
                                <button class="btn btn-primary rounded-pill" onclick="abrirMesa({{ $mesa->id }})">
                                    Abrir Mesa
                                </button>
                                @else
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning rounded-pill" onclick="verPedido({{ $mesa->id }})">
                                        Ver Pedido
                                    </button>
                                    <button class="btn btn-danger rounded-pill" onclick="cerrarMesa({{ $mesa->id }})">
                                        Cancelar Venta <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                @if($mesa->opened_at)
                                <div class="mt-2 text-muted small">
                                    Tiempo: <span id="contador-{{ $mesa->id }}">--:--</span>
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal para Abrir Mesa -->
<div class="modal fade" id="abrirMesaModal" tabindex="-1" aria-labelledby="abrirMesaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="abrirMesaModalLabel">Abrir Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <!-- Seleccionar Productos -->
                    <div class="form-group">
                        <label for="producto_id" class="col-sm-3 col-form-label text-start"><strong>Producto</strong></label>
                        <div class="col-md-12">
                            <input hidden type="number" class="form-control" name="producto_id" id="producto_id">
                            <input type="text" class="form-control" name="name" id="search-product" placeholder="Buscar Producto">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 col-form-label text-start"><strong>Categorías</strong></label>
                        <div class="mb-3">
                            @foreach ($productCategory as $category)
                            <button class="btn btn-outline-primary btn-sm m-1" type="button"
                                onclick="handleCategoryClick({{ $category->id }})">
                                {{ $category->nombre }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div id="product-container"></div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped text-xs">
                            <thead>
                                <tr class="text-center">
                                    <th>N°</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="row justify-content-end mb-3">
                        <div class="col-md-5 text-end">
                            <h5><strong>TOTAL: S/ <span id="totalAmount" name="total">0.00</span></strong></h5>
                            <input hidden type="number" step="0.01" name="total" id="totalAmountInput" value="0">
                            <input hidden type="date" name="fecha" id="fechaInput" value="{{ date('Y-m-d') }}">
                            <button class="btn me-2 mt-3 btn-warning" type="button" onclick="confirmOrder()">Confirmar</button>
                            <button class="btn me-2 mt-3 btn-secondary" type="button" onclick="preaccount()">Precuenta</button>
                            <button class="btn me-2 mt-3 btn-success" type="button" onclick="abrirModalCobro()">Cobrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cobro -->
<div class="modal fade" id="modalCobro" tabindex="-1" aria-labelledby="modalCobroLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <form id="formCobro">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Selección de Comprobante -->
                            <div class="mb-3">
                                <div class="btn-group d-flex justify-content-start mb-4">
                                    <input type="hidden" name="comprobante" id="comprobante" value="boleta">
                                    <button type="button" class="btn btn-outline-primary active me-1"
                                        onclick="seleccionarComprobante('boleta', event)">Boleta</button>
                                    <button type="button" class="btn btn-outline-success me-1"
                                        onclick="seleccionarComprobante('factura', event)">Factura</button>
                                    <button type="button" class="btn btn-outline-info me-1"
                                        onclick="seleccionarComprobante('ticket', event)">Ticket</button>
                                </div>
                            </div>

                            <!-- Documento y Cliente -->
                            <div class="mb-3">
                                <label class="form-label"><strong>DNI / RUC</strong></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="document" name="document" maxlength="11"
                                        onkeypress="isNumber(event)">
                                    <button class="btn btn-primary" type="button"
                                        onclick="searchAPI('#document','#client','#direccion')">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Cliente</strong></label>
                                <input type="text" class="form-control" id="client" name="client">
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Observación</strong></label>
                                <input type="text" id="observacion" name="observacion" class="form-control">
                            </div>

                            <input hidden type="number" name="type_sale" id="type_sale" value="0">
                            <input hidden type="number" name="status" id="status" value="0">
                        </div>

                        <div class="col-md-6">
                            <!-- Métodos de pago -->
                            <div class="mb-3">
                                <label class="mb-2"><strong>Método de Pago</strong></label>
                                <div class="d-flex flex-wrap">
                                    @foreach ($paymentMethod as $index => $method)
                                    @php
                                    $colorClass = $colors[$index % count($colors)];
                                    @endphp
                                    <button
                                        type="button"
                                        id="btn-{{ $method->id }}"
                                        class="btn {{ $colorClass }} me-2 mb-2"
                                        data-campos="campos-{{ $method->nombre }}"
                                        data-id="{{ $method->id }}"
                                        onclick="seleccionarMedioPago('{{ $method->id }}', event)">
                                        {{ strtoupper($method->nombre) }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Campos por método de pago -->
                            @foreach ($paymentMethod as $method)
                            <div class="mb-3 d-none align-items-center gap-3" id="campos-{{ $method->nombre }}">
                                <input type="hidden" name="medio_pago_id" value="{{ $method->id }}">
                                <label class="form-label mb-0">
                                    <strong>{{ strtoupper(Str::limit($method->nombre, 4, '.')) }}</strong>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="text" class="form-control" placeholder="Ingrese Monto"
                                        name="monto[{{ $method->id }}]" onkeypress="isDecimal(event)" oninput="calcularSaldo()">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <!-- Agrega esto donde quieras mostrar el saldo, por ejemplo debajo del total -->
                        <div class="mb-2 text-end d-none">
                            <strong>SALDO: S/ <span id="saldoAmount" class="text-danger">0.00</span></strong>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Finalizar Venta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
    function abrirModalCobro() {
        const modal = new bootstrap.Modal(document.getElementById('modalCobro'));
        modal.show();
    } 
    
    var serial = "{{ config('printer.serial') }}"
    let openedMesaId = null;
    let timerInterval;
    let selectedProducts = [];
    const mesaTimers = {}; // Asegúrate de declarar esto en el scope global
    const userSede = @json($userSede);
    const productSitePrices = @json($productSitePrices);

    const priceMap = productSitePrices.reduce((map, entry) => {
        map[`${entry.product_id}_${entry.headquarter_id}`] = entry.unit_price;
        return map;
    }, {});

    function getPriceBySede(productId) {
        const key = `${productId}_${userSede}`;
        return priceMap[key] ?? 0;
    }

    function seleccionarComprobante(comprobante, event) {
        const parent = event.target.closest('.btn-group');
        Array.from(parent.children).forEach(child => {
            child.classList.remove('active');
        });

        event.target.classList.add('active');
        document.getElementById('comprobante').value = comprobante;
    }

    function sincronizarMontosMetodosPago() {
        const contenedor = document.querySelector('.d-flex.flex-wrap');
        const botonesActivos = contenedor.querySelectorAll('.btn.active');

        if (botonesActivos.length === 1) {
            const botonActivo = botonesActivos[0];
            const camposId = botonActivo.dataset.campos;
            const idActivo = botonActivo.dataset.id;
            const total = document.getElementById('totalAmount').textContent.trim();

            $(`#${camposId} input[name="monto[${idActivo}]"]`).val(total);
        } else {
            botonesActivos.forEach(boton => {
                const camposId = boton.dataset.campos;
                $(`#${camposId} input[type="text"]`).val('');
            });
        }
    }

    const totalAmountSpan = document.getElementById('totalAmount');

    const observer = new MutationObserver(() => {
        sincronizarMontosMetodosPago();
    });

    observer.observe(totalAmountSpan, {
        childList: true, // Cambios en los nodos hijos (texto)
        characterData: true, // Cambios en texto directo
        subtree: true // Observar todo el subtree
    });

    function seleccionarMedioPago(medio_id, event) {
        const btn = event.target;
        btn.classList.toggle('active');

        const campos = btn.dataset.campos;

        // Toggle visibilidad campos monto
        $(`#${campos}`).toggleClass('d-none');

        // Limpiar input monto al abrir/cerrar
        $(`#${campos} input[name="monto[${medio_id}]"]`).val('');

        sincronizarMontosMetodosPago();
        calcularSaldo();

        if (!btn.classList.contains('active')) {
            btn.blur();
        }
    }

    function isDecimal(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;

        // Solo permite números y un solo punto decimal
        if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
            const input = evt.target || evt.srcElement;
            if (charCode === 46 && input.value.includes('.')) {
                evt.preventDefault();
                return false;
            }
            return true;
        } else {
            evt.preventDefault();
            return false;
        }
    }

    function isNumber(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;

        // Solo permite números (0–9)
        if (charCode < 48 || charCode > 57) {
            evt.preventDefault();
            return false;
        }

        return true;
    }

    function iniciarContadorMesa(id, openedAtStr) {
        const openedAt = new Date(openedAtStr);
        const span = document.getElementById(`contador-${id}`);
        const card = span.closest('.card-mesa');
        if (!span) return;

        setInterval(() => {
            const now = new Date();
            const diff = Math.floor((now - openedAt) / 1000);
            const min = String(Math.floor(diff / 60)).padStart(2, '0');
            const sec = String(diff % 60).padStart(2, '0');
            span.textContent = `${min}:${sec}`;
            // Si pasan más de 20 minutos, pinta de naranja
            if (diff >= 3600) {
                card.classList.add('borde-rojo');
                card.classList.remove('borde-naranja');
                card.classList.remove('borde-verde');
            } else if (diff >= 1200) {
                card.classList.remove('borde-rojo');
                card.classList.add('borde-naranja');
                card.classList.remove('borde-verde');
            } else {
                card.classList.remove('borde-rojo');
                card.classList.remove('borde-naranja');
                card.classList.add('borde-verde');
            }
        }, 1000);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($mesas as $mesa)
        @if($mesa->status === 'ocupada' && $mesa->opened_at)
        iniciarContadorMesa({{ $mesa->id }}, "{{ $mesa->opened_at }}");
        @endif
        @endforeach
    });

    // ADAPTACIÓN COMPLETA DEL ENVÍO DE COBRO PARA TU MODAL

    function calcularSaldo() {
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const totalVenta = parseFloat($('#totalAmount').text()) || 0;
        let totalPagado = 0;

        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;
            totalPagado += monto;
        });

        const saldo = Math.max(0, totalVenta - totalPagado);
        $('#saldoAmount').text(saldo.toFixed(2));

        // Cambiar color del saldo según el estado
        const saldoElement = $('#saldoAmount').parent();
        if (saldo > 0) {
            saldoElement.removeClass('text-success').addClass('text-danger');
        } else {
            saldoElement.removeClass('text-danger').addClass('text-success');
        }
    }

    document.getElementById('formCobro').addEventListener('submit', function(e) {
        e.preventDefault();

        const botonesMedioPago = document.querySelectorAll('.d-flex.flex-wrap button');
        const metodoPagoSeleccionado = Array.from(botonesMedioPago).some(btn => btn.classList.contains('active'));
        const comprobante = document.getElementById('comprobante').value;

        if (!metodoPagoSeleccionado) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar al menos un método de pago.'
            });
            return;
        }

        const saldoActual = parseFloat($('#saldoAmount').text()) || 0;
        if (saldoActual > 0) {
            ToastError.fire({
                text: 'Debe cancelar el monto completo antes de registrar la venta. El saldo actual es: S/ ' + saldoActual.toFixed(2)
            });
            return;
        }

        const form = this;
        const formData = new FormData(form);

        formData.append('products', JSON.stringify(selectedProducts));
        formData.append('total', document.getElementById('totalAmountInput').value);
        formData.append('fecha', document.getElementById('fechaInput').value);
        if (openedMesaId) formData.append('mesa_id', openedMesaId);

        const tipoComprobante = document.getElementById('comprobante').value;
        const voucherTypeFormatted = tipoComprobante.charAt(0).toUpperCase() + tipoComprobante.slice(1).toLowerCase();
        formData.append('voucher_type', voucherTypeFormatted);

        // Otros campos opcionales
        formData.append('observacion', document.getElementById('observacion').value || '');
        formData.append('type_sale', document.getElementById('type_sale').value || '0');
        formData.append('status', document.getElementById('status').value || '0');
        // PARA GUARDAR COMO RESTAURANT, si no se manda nada predeterminado es 0 asi que no problem
        formData.append('restaurant', 1);

        const resetFormulario = () => {
            selectedProducts = [];
            renderProductTable();
            document.getElementById('formCobro').reset();
            document.getElementById('totalAmount').textContent = '0.00';
            document.getElementById('totalAmountInput').value = 0;
        };

        fetch(`{{ route('sales.store') }}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(res => res.json())
            .then(response => {
                console.log(response.status)
                if (response.status) {
                        const venta = response.venta;
                        const cliente = venta.cliente || 'N/A';
                        const documento = venta.documento || 'N/A';
                        const fecha = venta.fecha || new Date().toLocaleDateString();
                        const observacion = venta.observacion || '';
                        const total = parseFloat(venta.total || 0).toFixed(2);
                        const productos = venta.productos || [];
                        const pagos = venta.pagos || []; // Si tienes info de pagos en backend
                        const type_sale = venta.type_sale || 0;
                        const status = venta.status || 0;

                        let productosHtml = '';
                        productos.forEach(p => {
                            productosHtml += `<p>${p.cantidad}x ${p.nombre} - S/ ${(p.precio * p.cantidad).toFixed(2)}</p>`;
                        });

                        let pagosHtml = '';
                        if (pagos.length > 0) {
                            pagos.forEach(pago => {
                                pagosHtml += `<p>${pago.nombre}: S/ ${parseFloat(pago.monto).toFixed(2)}</p>`;
                            });
                        } else {
                            pagosHtml = '<p>(Sin métodos de pago registrados)</p>';
                        }

                        const html = `
                            <html>
                            <body>
                                <div class="center bold">TICKET - ${venta.id}</div>
                                <p>Fecha: ${fecha}</p>
                                <p>Cliente: ${cliente}</p>
                                <p>DNI/RUC: ${documento}</p>
                                <p>Observación: ${observacion}</p>
                                <hr>
                                ${productosHtml}
                                <hr>
                                <p><strong>TOTAL: S/ ${total}</strong></p>
                                <hr>
                                <p>Formas de pago:</p>
                                ${pagosHtml}
                                <p>Gracias por su compra</p>
                            </body>
                            </html>
                            `;

                        $('#modalCobro').modal('hide');
                        $('#abrirMesaModal').modal('hide');
                        clearInterval(timerInterval);
                        imprimirVenta(response.venta.id);
                        ToastMessage.fire({
                            text: 'Venta registrada correctamente'
                        });
                        cerrarMesaFrom(openedMesaId); // <-- LLAMA AQUÍ
                        resetFormulario();        // <-- Y luego limpia el formulario
                    } else {
                        ToastError.fire({
                            text: response.message || 'Error al registrar venta'
                        });
                    }
            })
            .catch((error) => {
                console.log(error);
                ToastError.fire({
                    text: 'Error de red al enviar la venta'
                });
            });
    });

    var productsSelect = @json($products);
    $('#search-product').autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();

            const results = productsSelect
                .filter(p => p.nombre.toLowerCase().includes(term))
                .map(p => ({
                    label: p.nombre,
                    value: p.nombre,
                    id: p.id,
                }));

            response(results);
        },
        appendTo: '#abrirMesaModal',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            handleProductClickSelect(ui.item.id, ui.item.value);
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };

    function searchAPI(docEl, nameEl, addressEl) {
        var doc = $(docEl).val();

        $(nameEl).val('');
        $(addressEl).val('');
        $('#client').val('');

        if (doc.length != 8 && doc.length != 11) {
            return;
        }

        Swal.showLoading();

        $.ajax({
            url: "{{ url('sunat/consultar') }}?doc=" + doc,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    if (doc.length === 8) {
                        var fullName = `${data.nombre} ${data.apellido_paterno} ${data.apellido_materno}`;
                        $(nameEl).val(fullName);
                        $(addressEl).val(data.domicilio?.direccion || '');
                        $('#client').val(fullName);
                    } else {
                        $(nameEl).val(data.nombre);
                        $(addressEl).val(data.domicilio?.direccion || '');
                        $('#client').val(data.nombre);
                    }
                } else {
                    ToastError.fire({ text: response.message || 'No se encontró información' });
                }
                Swal.close();
            },
            error: function(xhr) {
                ToastError.fire({ text: 'Error al consultar SUNAT/RENIEC' });
                Swal.close();
            }
        });
    }

    function abrirMesa(mesaId) {
        fetch(`{{ url('/mesas/abrir') }}/${mesaId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            })
            .then(res => {
                if (!res.ok) throw new Error("Error al abrir mesa");
                return res.json();
            })
            .then(data => {
                // ✅ ACTUALIZAR ESTADO
                const estadoSpan = document.getElementById(`estado-mesa-${mesaId}`);
                if (estadoSpan) {
                    estadoSpan.textContent = 'Ocupada';
                    estadoSpan.classList.remove('bg-success');
                    estadoSpan.classList.add('bg-danger');
                }

                // ✅ REEMPLAZAR ACCIONES
                const accionesDiv = document.getElementById(`acciones-mesa-${mesaId}`);
                if (accionesDiv) {
                    accionesDiv.innerHTML = `
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning rounded-pill" onclick="verPedido(${mesaId})">
                            Ver Pedido
                        </button>
                        <button class="btn btn-danger rounded-pill" onclick="cerrarMesa(${mesaId})">
                            Cancelar Venta <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-muted small">
                        Tiempo: <span id="contador-${mesaId}">--:--</span>
                    </div>
                `;
                }

                // ✅ CONTADOR Y COLOR DINÁMICO
                const openedAt = new Date(data.opened_at);
                const contadorEl = document.getElementById(`contador-${mesaId}`);
                const card = document.getElementById(`mesa-card-${mesaId}`);

                if (contadorEl && card) {
                    const intervalId = setInterval(() => {
                        const now = new Date();
                        const diff = Math.floor((now - openedAt) / 1000);
                        const min = String(Math.floor(diff / 60)).padStart(2, '0');
                        const sec = String(diff % 60).padStart(2, '0');
                        contadorEl.textContent = `${min}:${sec}`;

                        // Cambiar borde por tiempo
                        if (diff >= 3600) {
                            card.classList.add('borde-rojo');
                            card.classList.remove('borde-naranja', 'borde-verde');
                        } else if (diff >= 1200) {
                            card.classList.add('borde-naranja');
                            card.classList.remove('borde-rojo', 'borde-verde');
                        } else {
                            card.classList.add('borde-verde');
                            card.classList.remove('borde-naranja', 'borde-rojo');
                        }
                    }, 1000);

                    mesaTimers[mesaId] = intervalId; // Guardar para limpiar luego
                }

                // ✅ LIMPIAR INFO DE PEDIDO PREVIO
                selectedProducts = [];
                renderProductTable();
                currentOrderId = null;
                openedMesaId = null;
                $('#document').val('');
                $('#client').val('');
                $('#observacion').val('');
                $('#totalAmount').text('0.00');
                $('#totalAmountInput').val('0');
                document.querySelectorAll("input[name^='monto']").forEach(el => el.value = '');

                // ✅ GUARDAR INFO ACTUAL
                openedMesaId = mesaId;
                currentOrderId = data.order_id;

                $('#abrirMesaModal').modal('show');
            })
            .catch(error => {
                console.error(error);
                alert("No se pudo abrir la mesa.");
            });
    }

    function handleCategoryClick(categoryId) {
        const allProducts = @json($productCategory);

        selectedCategory = allProducts.find(category => category.id === categoryId);

        const productContainer = document.getElementById('product-container');

        productContainer.innerHTML = '';

        if (selectedCategory) {
            selectedCategory.productos.forEach(producto => {
                const productElement = document.createElement('button');
                productElement.className = "btn btn-outline-success btn-sm m-1";
                productElement.type = "button";
                productElement.textContent = producto.nombre.toUpperCase();
                productElement.onclick = function() {
                    handleProductClick(producto.id, producto.nombre);
                };
                productContainer.appendChild(productElement);
            });
        }
    }

    function handleProductClick(productId, productName) {
        if (!selectedCategory) return;
        const product = selectedCategory.productos.find(
            p => p.id === productId && (p.nombre.toLowerCase() === productName.toLowerCase())
        );
        if (!product) return;

        const precioSegunSede = product.precio_final ?? 0;


        const existing = selectedProducts.find(
            p => p.id === productId && (p.nombre.toLowerCase() === productName.toLowerCase()) //p => p.id === productId
        );
        if (!existing) {
            selectedProducts.push({
                id: product.id,
                nombre: product.nombre,
                precio: precioSegunSede,
                cantidad: 1
            });
        } else {
            existing.cantidad = toNum(existing.cantidad) + 1;
        }

        // ✅ Usar la función con debounce
        agregarProductoClick({ id: product.id, precio: precioSegunSede, nombre: product.nombre });
        renderProductTable();

        const $input = $('#search-product');
        $input.val('');
    }

     function handleProductClickSelect(productId, productName) {
        const products = @json($products);

        const product = products.find(
            p => p.id === productId && (p.nombre.toLowerCase() === productName.toLowerCase())
        );
        if (!product) return;

        const precioSegunSede = product.precio_final ?? 0;


        const existing = selectedProducts.find(p => p.id === productId);
        if (!existing) {
            selectedProducts.push({
                id: product.id,
                nombre: product.nombre,
                precio: precioSegunSede,
                cantidad: 1
            });
        } else {
            existing.cantidad = toNum(existing.cantidad) + 1;
        }
        // ✅ Usar la función con debounce
        agregarProductoClick({ id: product.id, precio: precioSegunSede, nombre: product.nombre });
        renderProductTable();
        const $input = $('#search-product');
        $input.val('');
    }

    function renderProductTable() {
        const tbody = document.querySelector('tbody');
        tbody.innerHTML = '';

        if (selectedProducts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Seleccione un Producto</td>
                </tr>
            `;
            updateTotal();
            sincronizarMontosMetodosPago();
            return;
        }

        selectedProducts.forEach((p, index) => {
            const precio = parseFloat(p.precio) || 0;
            const cantidad = parseFloat(p.cantidad) || 0;
            const subtotal = (precio * cantidad).toFixed(2);

            const row = `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td hidden class="text-center">${p.id}</td>
                    <td class="text-center">${p.nombre}</td>
                    <td class="text-center">
                        <input type="number" 
                            id="quantity-${index}" 
                            class="form-control cantidad-input" 
                            min="1" 
                            value="${p.cantidad}"
                            oninput="updateSubtotal(${index})" 
                            style="width: 100px;">
                    </td>
                    <td class="text-center">
                        S/ ${precio.toFixed(2)}
                    </td>
                    <td class="text-center subtotal-container">
                    S/ ${subtotal} 
                    ${ p.confirmado === 1 ? '<i class="bi bi-check2-square" title="Confirmado"></i></button>' : ''}
                    </td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        updateTotal();
    }

    function removeItem(index) {
        const productToRemove = selectedProducts[index];

        if (!productToRemove) return;

        Swal.fire({
            title: '¿Eliminar producto?',
            text: `¿Seguro que deseas eliminar "${productToRemove.nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Deshabilitar todos los botones de eliminar
                const deleteButtons = document.querySelectorAll('.btn-danger');
                deleteButtons.forEach(btn => btn.disabled = true);

                // Mostrar indicador de carga
                const loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) loadingIndicator.style.display = 'block';

                eliminarProductoDelPedido(productToRemove.id)
                    .then(() => {
                        // Si la eliminación fue exitosa, quitarlo del array
                        selectedProducts.splice(index, 1);
                        renderProductTable();

                        ToastSuccess.fire({
                            text: 'Producto eliminado correctamente.'
                        });
                    })
                    .catch(error => {
                        console.error('Error al eliminar producto:', error);
                        ToastError.fire({
                            text: 'Error al eliminar el producto. Inténtelo nuevamente.'
                        });
                    })
                    .finally(() => {
                        // Ocultar indicador de carga y reactivar botones
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                        deleteButtons.forEach(btn => btn.disabled = false);
                    });
            }
        });
    }

    const ToastSuccess = Swal.mixin({
        toast: true,
        position: 'top-end',
        icon: 'success',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    let debounceTimers = {};

    function updateSubtotal(index) {
        const input = document.getElementById(`quantity-${index}`);
        const raw = parseFloat(input.value);
        const newQuantity = Number.isFinite(raw) ? toNum(raw, 3) : 0;
        const item = selectedProducts[index];

        item.cantidad = newQuantity;

        if (debounceTimers[index]) clearTimeout(debounceTimers[index]);
        debounceTimers[index] = setTimeout(() => {
            guardarProductoEnPedido({
            id: item.id,
            nombre: (item.id === 238 ? (item.nombre ?? null) : null),
            cantidad: newQuantity,
            precio: item.precio,
            sumar: false 
            });
        }, 800);

        updateSubtotalDisplay(index);
        updateTotal();
    }


    // Nueva función para actualizar solo el display del subtotal
    function updateSubtotalDisplay(index) {
        const row = document.querySelector(`#quantity-${index}`).closest('tr');
        const subtotalCell = row.cells[5]; // La celda del subtotal
        const precio = parseFloat(selectedProducts[index].precio) || 0;
        const cantidad = parseFloat(selectedProducts[index].cantidad);
        // Si cantidad es NaN (input vacío o inválido), mostrar 0
        const subtotal = (!isNaN(cantidad) ? precio * cantidad : 0).toFixed(2);
        subtotalCell.textContent = `S/ ${subtotal}`;
    }

    const productoBuffer = {};
    const temporizadores = {};
    const mkKey = (id, nombre) => `${id}::${(nombre || '').toLowerCase()}`;

    function agregarProductoClick(producto) {
        if (!currentOrderId) {
            alert("No hay una orden activa.");
            return;
        }

        const key = mkKey(producto.id, producto.nombre);
        if (!productoBuffer[key]) {
            productoBuffer[key] = {
            id: producto.id,
            precio: toNum(producto.precio, 2),
            cantidad: 0,
            nombre: producto.nombre ?? null
            };
        }
        productoBuffer[key].cantidad = toNum(productoBuffer[key].cantidad) + 1;

        if (temporizadores[key]) clearTimeout(temporizadores[key]);
        temporizadores[key] = setTimeout(() => {
            const acumulado = { ...productoBuffer[key] };
            delete productoBuffer[key];
            delete temporizadores[key];

            // 👇 aquí la CLAVE: sumar: true
            guardarProductoEnPedido({
            id: acumulado.id,
            nombre: (acumulado.id === 238 ? (acumulado.nombre ?? null) : null),
            cantidad: acumulado.cantidad,
            precio: acumulado.precio,
            sumar: true
            });
        }, 800);
    }


    function guardarProductoEnPedido(producto) {
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) loadingIndicator.style.display = 'block';

        // Solo para 238 mandamos el nombre (puede ser null si no aplica)
        const nombreEnviar = (producto.id === 238) ? (producto.nombre ?? null) : null;

        console.log("🟢 Enviando producto FINAL acumulado:", {
            product_id: producto.id,
            cantidad: producto.cantidad,
            precio_unitario: producto.precio,
            nombre: nombreEnviar,
            sumar: !!producto.sumar
        });

        fetch(`{{ url('/orders') }}/${currentOrderId}/addproduct`, {
            method: 'POST',
            headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
            },
            body: JSON.stringify({
            product_id: producto.id,
            cantidad: producto.cantidad,
            precio_unitario: producto.precio,
            nombre: nombreEnviar,
            sumar: !!producto.sumar
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log("✅ Producto guardado exitosamente:", data);
        })
        .catch(error => {
            console.error('❌ Error al guardar producto:', error);
            alert("Error de conexión. Intenta de nuevo.");
        })
        .finally(() => {
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        });
    }


    function eliminarProductoDelPedido(productId) {
        if (!currentOrderId) {
            console.error("No hay orden activa");
            return Promise.reject(new Error("No hay orden activa"));
        }

        console.log("Eliminando producto:", productId, "de la orden:", currentOrderId);

        return fetch(`{{ url('/orders') }}/${currentOrderId}/removeproduct`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => {
                console.log('Status:', response.status);
                console.log('Status Text:', response.statusText);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return response.json();
            })
            .then(data => {
                console.log('Respuesta al eliminar producto:', data);
                if (data.success) {
                    console.log('Producto eliminado exitosamente del backend');
                } else {
                    console.error('Error del servidor:', data.message);
                    alert("Error al eliminar el producto: " + (data.message || "Error desconocido"));
                }
            })
            .catch(error => {
                console.error('Error al eliminar producto:', error);
                alert("Error de conexión al eliminar el producto. Verifique su conexión.");
            });
    }

    const toNum = (v, dec = null) => {
    const n = parseFloat(v);
        if (!Number.isFinite(n)) return 0;
        return dec === null ? n : +n.toFixed(dec);
    };

    function verPedido(mesaId) {
        fetch(`{{ url('/mesas/pedido') }}/${mesaId}`)
            .then(res => {
                if (!res.ok) throw new Error("Error al obtener el pedido.");
                return res.json();
            })
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    return;
                }

                selectedProducts = (data.productos || []).map(p => ({
                    ...p,
                    cantidad: toNum(p.cantidad, 3),
                    precio:   toNum(p.precio,   2),
                }));
                currentOrderId = data.order_id;
                openedMesaId = mesaId;
                renderProductTable();
                $('#abrirMesaModal').modal('show');
            })
            .catch(err => {
                console.error('Error al cargar pedido:', err);
                alert("Error al cargar el pedido.");
            });
    }

    // Vuelve a tener la función updateTotal simple
    function updateTotal() {
        let total = 0;
        selectedProducts.forEach(p => {
            total += (parseFloat(p.precio) || 0) * (parseFloat(p.cantidad) || 0);
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2);
        document.getElementById('totalAmountInput').value = total.toFixed(2);
    }

    function cerrarMesaFrom(mesaId) {
        fetch(`{{ url('/mesas') }}/${mesaId}/cerrar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mesa liberada',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // ✅ Restaurar estado visual
                    const estadoSpan = document.getElementById(`estado-mesa-${mesaId}`);
                    if (estadoSpan) {
                        estadoSpan.textContent = 'Libre';
                        estadoSpan.classList.remove('bg-danger');
                        estadoSpan.classList.add('bg-success');
                    }

                    const accionesDiv = document.getElementById(`acciones-mesa-${mesaId}`);
                    if (accionesDiv) {
                        accionesDiv.innerHTML = `
                        <button class="btn btn-primary rounded-pill" onclick="abrirMesa(${mesaId})">
                            Abrir Mesa
                        </button>
                    `;
                    }

                    // ✅ Eliminar el contador visual
                    const contador = document.getElementById(`contador-${mesaId}`);
                    if (contador) {
                        contador.remove();
                    }

                    // ✅ Limpiar el intervalo del contador
                    if (mesaTimers[mesaId]) {
                        clearInterval(mesaTimers[mesaId]);
                        delete mesaTimers[mesaId];
                    }
                    // ✅ Quitar todos los bordes de color
                    const card = document.getElementById(`mesa-card-${mesaId}`);
                    if (card) {
                        card.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');
                        card.style.border = 'none';
                    }


                } else {
                    Swal.fire('Error', data.message || 'No se pudo cerrar la mesa.', 'error');
                }
            })
            .catch(err => {
                console.error('Error al cerrar la mesa:', err);
                Swal.fire('Error', 'Error inesperado al cerrar la mesa.', 'error');
            });
    }

    function cerrarMesa(mesaId) {
        Swal.fire({
            title: '¿Liberar mesa?',
            text: 'Esto eliminará el pedido y liberará la mesa.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, liberar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/mesas') }}/${mesaId}/cerrar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Mesa liberada',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
    
                            // ✅ Restaurar estado visual
                            const estadoSpan = document.getElementById(`estado-mesa-${mesaId}`);
                            if (estadoSpan) {
                                estadoSpan.textContent = 'Libre';
                                estadoSpan.classList.remove('bg-danger');
                                estadoSpan.classList.add('bg-success');
                            }
    
                            const accionesDiv = document.getElementById(`acciones-mesa-${mesaId}`);
                            if (accionesDiv) {
                                accionesDiv.innerHTML = `
                                <button class="btn btn-primary rounded-pill" onclick="abrirMesa(${mesaId})">
                                    Abrir Mesa
                                </button>
                            `;
                            }

                             
                            // ✅ Quitar todos los bordes de color
                            const card = document.getElementById(`mesa-card-${mesaId}`);
                            if (card) {
                                card.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');
                                card.style.border = 'none';
                            }
    
                            // ✅ Eliminar el contador visual
                            const contador = document.getElementById(`contador-${mesaId}`);
                            if (contador) {
                                contador.remove();
                            }
    
                            // ✅ Limpiar el intervalo del contador
                            if (mesaTimers[mesaId]) {
                                clearInterval(mesaTimers[mesaId]);
                                delete mesaTimers[mesaId];
                            }

    
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo cerrar la mesa.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error al cerrar la mesa:', err);
                        Swal.fire('Error', 'Error inesperado al cerrar la mesa.', 'error');
                    });
            }
        });

    }   

    let currentProductIndex = -1;

    function convertirMontoALetras(monto) {
        const [entero, decimal] = monto.toFixed(2).split('.');
        const parteEntera = parseInt(entero);
        const centavos = parseInt(decimal);

        let resultado = '';

        if (parteEntera === 0) {
            resultado = 'cero soles';
        } else if (parteEntera === 1) {
            resultado = 'un sol';
        } else if (parteEntera < 1000) {
            resultado = numeroALetras(parteEntera) + ' soles';
        } else {
            // Para miles
            const miles = Math.floor(parteEntera / 1000);
            const resto = parteEntera % 1000;

            if (miles === 1) {
                resultado = 'mil';
            } else {
                resultado = numeroALetras(miles) + ' mil';
            }

            if (resto > 0) {
                resultado += ' ' + numeroALetras(resto);
            }

            resultado += ' soles';
        }

        // Agregar centavos
        if (centavos > 0) {
            resultado += ' con ' + numeroALetras(centavos) + ' céntimos';
        }

        return resultado.toUpperCase();
    }

    function numeroALetras(num) {
        const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        if (num === 0) return 'cero';
        if (num === 100) return 'cien';

        let resultado = '';

        // Centenas
        if (num >= 100) {
            resultado += centenas[Math.floor(num / 100)] + ' ';
            num %= 100;
        }

        // Decenas y unidades
        if (num >= 20) {
            resultado += decenas[Math.floor(num / 10)];
            if (num % 10 !== 0) {
                resultado += ' y ' + unidades[num % 10];
            }
        } else if (num >= 10) {
            resultado += especiales[num - 10];
        } else if (num > 0) {
            resultado += unidades[num];
        }

        return resultado.trim();
    }

    function imprimirVenta(saleId) {
        $.ajax({
            url: "{{ route('anticipated_print') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                sale_id: saleId
            },
            success: async function(response) {
                if (!response.status) {
                    ToastError.fire({
                        text: response.error || 'Error al obtener datos de la venta'
                    });
                    return;
                }

                const data = response;
                const venta = data.venta;
                const productos = data.productos;
                const pagos = data.pagos;
                const voucherType = (venta.voucher_type || '').toLowerCase();

                // Formato especial para boleta/factura
                if (voucherType === 'boleta' || voucherType === 'factura') {
                    // Calcular OP. GRAVADA e IGV
                    let opGravada = 0;
                    let igv = 0;
                    let total = 0;
                    let productosLineas = [];

                    productos.forEach(function(producto) {
                        const cantidad = parseFloat(producto.cantidad) || 0;
                        const precio = parseFloat(producto.precio) || 0;
                        const subtotal = parseFloat(producto.subtotal) || (cantidad * precio);
                        opGravada += subtotal;
                        productosLineas.push({
                            nombre: producto.nombre,
                            cantidad: cantidad,
                            precio: precio,
                            subtotal: subtotal
                        });
                    });

                    let opGravadaSinIGV = opGravada / 1.18;
                    igv = opGravada - opGravadaSinIGV;
                    total = opGravada;

                    let operaciones = [{
                            nombre: "Iniciar",
                            argumentos: []
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [1]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [true]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["MUSAS PASTELERIA S.R.L.\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [false]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["RUC 20611061618\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["AV. JOSE BALTA NRO. 054 P.J. CHINO ZAMORA CHICLAYO\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["CHICLAYO LAMBAYEQUE\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["=================================================\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [true]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [voucherType === 'boleta' ? "BOLETA DE VENTA ELECTRÓNICA\n" : "FACTURA ELECTRÓNICA\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [false]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`${venta.number || ''}\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [
                                voucherType === 'factura' ?
                                `RAZON SOCIAL: ${venta.cliente || 'CLIENTE VARIOS'}\n` :
                                `NOMBRE: ${venta.cliente || 'CLIENTE VARIOS'}\n`
                            ]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [
                                voucherType === 'factura' ?
                                `RUC: ${venta.document || '00000000000'}\n` :
                                `DNI: ${venta.document || '00000000'}\n`
                            ]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`EMISION: ${data.now || ''}\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["MONEDA:  SOL (PEN)\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["METODOS DE PAGO\n"]
                        }
                    ];

                    // Agregar métodos de pago
                    if (pagos && pagos.length > 0) {
                        pagos.forEach(function(pago) {
                            operaciones.push({
                                nombre: 'EscribirTexto',
                                argumentos: [`${pago.metodo_pago}: S/${parseFloat(pago.monto).toFixed(2)}\n`]
                            });
                        });
                    }

                    // Agregar productos
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["------------------------------------------------\n"]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: ['CODIGO DESCRIPCION   CANT   P.UNIT   P.TOTAL\n']
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["-------------------------------------------------\n"]
                    });

                    productosLineas.forEach(function(prod) {
                        // Divide el nombre en líneas de máximo 20 caracteres
                        let nombre = prod.nombre;
                        let lineas = [];
                        while (nombre.length > 20) {
                            lineas.push(nombre.substring(0, 20));
                            nombre = nombre.substring(20);
                        }
                        if (nombre.length > 0) lineas.push(nombre);

                        // Imprime la primera línea con las columnas
                        let cantidad = prod.cantidad.toFixed(2).padStart(5);
                        let precio = prod.precio.toFixed(2).padStart(8);
                        let subtotal = prod.subtotal.toFixed(2).padStart(8);
                        operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [lineas[0].padEnd(20) + cantidad + precio + subtotal + '\n']
                        });

                        // Imprime las siguientes líneas solo con el nombre
                        for (let i = 1; i < lineas.length; i++) {
                            operaciones.push({
                                nombre: 'EscribirTexto',
                                argumentos: [lineas[i] + '\n']
                            });
                        }
                    });

                    // Totales
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["------------------------------------------------\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["OP. GRAVADA   : S/ " + opGravadaSinIGV.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["IGV           : S/ " + igv.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["IMPORTE TOTAL : S/ " + total.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["SON: " + convertirMontoALetras(total) + "\n"]
                    });

                    // Información adicional
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["\nINFORMACION ADICIONAL:\n"]
                    });

                    // Agrega dirección si existe
                    if (venta.direccion) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`DIRECCION: ${venta.direccion}\n`]
                        });
                    }

                    // Agrega referencia si existe
                    if (venta.referencia) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`REFERENCIA: ${venta.referencia}\n`]
                        });
                    }

                    // Agrega teléfono si existe
                    if (venta.telefono) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`TELEFONO: ${venta.telefono}\n`]
                        });
                    }

                    // Agrega sede recojo si existe
                    if (venta.sede_recojo) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`SEDE RECOJO: ${venta.sede_recojo}\n`]
                        });
                    }

                    // Agrega usuario si existe
                    if (venta.user_id) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`USUARIO: ${venta.user_id}\n`]
                        });
                    }

                    // Agrega fecha de entrega si existe
                    if (venta.fecha_entrega) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`FECHA ENTREGA: ${venta.fecha_entrega}\n`]
                        });
                    }

                    // Agrega hora de entrega si existe
                    if (venta.hora_entrega) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`HORA ENTREGA: ${venta.hora_entrega}\n`]
                        });
                    }

                    // Agrega observación si existe
                    if (venta.observacion) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`OBSERVACION: ${venta.observacion}\n`]
                        });
                    }
                    // ...existing code...

                    // Footer
                    operaciones.push({
                        nombre: "Feed",
                        argumentos: [2]
                    }, {
                        nombre: "EstablecerAlineacion",
                        argumentos: [1]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["Gracias por su preferencia\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["Implementado por xinergia.net\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: [`IMPRESION: ${data.now}\n`]
                    }, {
                        nombre: "Feed",
                        argumentos: [1]
                    }, {
                        nombre: "Corte",
                        argumentos: [1]
                    });

                    // IMPRESIÓN DE BOLETA/FACTURA
                    try {
                        // Intentar impresión local primero
                        const http = await fetch('http://localhost:8000/imprimir', {
                            method: 'POST',
                            // headers: {
                            //     'Content-Type': 'application/json'
                            // },
                            body: JSON.stringify({
                                serial: serial,
                                nombreImpresora: 'Ticketera',
                                operaciones: operaciones
                            })
                        });

                        const res = await http.json();
                        if (!res.ok) {
                            throw new Error(res.message || 'Error al imprimir localmente');
                        } else {
                            ToastMessage.fire({
                                text: 'Comprobante impreso correctamente'
                            });
                        }
                    } catch (error) {
                        console.log('Error en impresión local, intentando remota:', error.message);

                        // Si falla local, intentar impresión remota
                        try {
                            const rutaRemota = `http://192.168.18.46:8000/imprimir`;
                            const payload = {
                                operaciones: operaciones,
                                nombreImpresora: 'Ticketera',
                                serial: serial,
                            };

                            const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                method: 'POST',
                                body: JSON.stringify(payload),
                                // headers: {
                                //     'Content-Type': 'application/json; charset=utf-8'
                                // }
                            });

                            const remoteRes = await remoteResponse.json();
                            if (remoteRes.ok) {
                                ToastMessage.fire({
                                    text: 'Comprobante impreso correctamente (Remoto)'
                                });
                            } else {
                                throw new Error('Impresión remota falló: ' + remoteRes.message);
                            }
                        } catch (errorRemoto) {
                            console.error('Error al imprimir boleta/factura:', errorRemoto);
                            ToastError.fire({
                                text: 'Error al imprimir la boleta/factura: ' + errorRemoto.message
                            });
                            return;
                        }
                    }

                    // Si llegó aquí, la impresión fue exitosa, terminar función
                    return;
                }

                // FORMATO ORIGINAL PARA TICKET (solo si NO es boleta/factura)
                const opts = {
                    serial: serial,
                    nombreImpresora: 'Ticketera',
                    operaciones: [{
                            nombre: 'Iniciar',
                            argumentos: []
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [1]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['MUSAS PASTELERIA\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`000${venta.type_sale} - ${venta.tipo || 'N/A'}\n`]
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [0]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`NUMERO: ${venta.number || 'N/A'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`SEDE: ${venta.sede}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`USUARIO: ${venta.user_id || 'Usuario'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`FECHA VENTA: ${venta.fecha}\n`]
                        }
                    ]
                };

                // Métodos de pago
                if (pagos && pagos.length > 0) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: ['METODOS DE PAGO:\n']
                    });
                    pagos.forEach(function(pago) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${pago.metodo_pago}: S/${pago.monto}\n`]
                        });
                    });
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: ['----------------------------------------\n']
                    });
                }

                // Productos
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['PRODUCTOS:\n']
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['CANT PRODUCTO        P.U     TOTAL\n']
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });

                productos.forEach(function(producto) {
                    const cant = producto.cantidad.toString().padEnd(4);
                    const precio = `S/${parseFloat(producto.precio).toFixed(2)}`.padStart(8);
                    const total = `S/${parseFloat(producto.subtotal).toFixed(2)}`.padStart(8);

                    if (producto.nombre.length > 15) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${cant} ${producto.nombre}\n`]
                        });
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${' '.repeat(19)} ${precio} ${total}\n`]
                        });
                    } else {
                        const nombre = producto.nombre.padEnd(15);
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${cant} ${nombre} ${precio} ${total}\n`]
                        });
                    }
                });

                // Footer del ticket
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [2]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: [`TOTAL: S/${parseFloat(venta.total).toFixed(2)}\n`]
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [0]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['INFORMACION ADICIONAL\n']
                });

                if (venta.direccion) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`DIRECCION: ${venta.direccion}\n`]
                    });
                }
                if (venta.referencia) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`REFERENCIA: ${venta.referencia}\n`]
                    });
                }
                if (venta.observacion) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`OBSERVACION: ${venta.observacion}\n`]
                    });
                }

                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                }, {
                    nombre: "EstablecerAlineacion",
                    argumentos: [0]
                }, {
                    nombre: 'Feed',
                    argumentos: [2]
                }, {
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['Gracias por su preferencia\n']
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['Implementado por xinergia.net\n']
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: [`IMPRESION: ${data.now}\n`]
                }, {
                    nombre: 'Feed',
                    argumentos: [1]
                }, {
                    nombre: 'Corte',
                    argumentos: [1]
                });

                // IMPRESIÓN DEL TICKET
                try {
                    // Intentar impresión local primero
                    const http = await fetch('http://localhost:8000/imprimir', {
                        method: 'POST',
                        /* headers: {
                            'Content-Type': 'application/json'
                        }, */
                        body: JSON.stringify({
                            serial: serial,
                            nombreImpresora: 'Ticketera',
                            operaciones: opts.operaciones
                        })
                    });

                    const res = await http.json();
                    if (!res.ok) {
                        throw new Error(res.message || 'Error al imprimir localmente');
                    } else {
                        ToastMessage.fire({
                            text: 'Ticket impreso correctamente'
                        });
                    }
                } catch (error) {
                    console.log('Error en impresión local, intentando remota:', error.message);

                    // Si falla local, intentar impresión remota
                    try {
                        const rutaRemota = `http://192.168.18.46:8000/imprimir`;
                        const payload = {
                            operaciones: opts.operaciones,
                            nombreImpresora: 'Ticketera',
                            serial: serial,
                        };

                        const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                            method: 'POST',
                            body: JSON.stringify(payload),
                            /* headers: {
                                'Content-Type': 'application/json; charset=utf-8'
                            } */
                        });

                        const remoteRes = await remoteResponse.json();
                        if (remoteRes.ok) {
                            ToastMessage.fire({
                                text: 'Ticket impreso correctamente (Remoto)'
                            });
                        } else {
                            throw new Error('Impresión remota falló: ' + remoteRes.message);
                        }
                    } catch (errorRemoto) {
                        console.error('Error al imprimir ticket:', errorRemoto);
                        ToastError.fire({
                            text: 'Error al imprimir el ticket: ' + errorRemoto.message
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error en la solicitud:', error);
                ToastError.fire({
                    text: 'Error al obtener datos para impresión'
                });
            }
        });
    }

    function confirmOrder(showModal = true) {
		var order_id = currentOrderId;
		$.ajax({
			url: '{{ route('orders.confirm') }}',
			method: 'post',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
			data: {
				order_id
			},
			success: async function(response) {
				if (response.status) {
                    var table = response.table;
                    var details = response.details;
                    mostrarIconoConfirmado();

                    const opts = {
                        serial: serial,
                        nombreImpresora: 'Ticketera',
                        operaciones: [{
                                nombre: 'Iniciar',
                                argumentos: []
                            },
                            {
                                nombre: "EstablecerAlineacion",
                                argumentos: [1]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['PREPARACION\n']
                            },
                            {
                                nombre: "EstablecerAlineacion",
                                argumentos: [0]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: [`FECHA: ${(new Date()).toLocaleDateString('es-PE')} ${(new Date()).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })}\n`]
                            },
                            {
                                nombre: 'TextoSegunPaginaDeCodigos',
                                argumentos: [
                                    2,
                                    'cp850',
                                    `MESA: ${table}\n`
                                ]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'EstablecerEnfatizado',
                                argumentos: [true]
                            }
                        ]
                    };

                    details.forEach(function(order) {
                        opts.operaciones.push({
                            nombre: 'TextoSegunPaginaDeCodigos',
                            argumentos: [
                                2,
                                'cp850',
                                `${order.cantidad}    ${order.product.nombre}\n`
                            ]
                        }, );
                    });

                    opts.operaciones.push({
                        nombre: 'Corte',
                        argumentos: [1]
                    });

                    try {
                        // IP de la PC que tiene la impresora (cámbiala por la tuya)
                        const IP_PC_IMPRESORA = '192.168.18.46';

                        let url;
                        let headers = {
                            'Content-Type': 'application/json; charset=utf-8'
                        };

                        // Verificar si estamos en Android o PC
                        let esAndroid = false;
                        try {
                            const platformResponse = await fetch('http://localhost:8000/version', {
                                timeout: 3000 // Timeout de 3 segundos
                            });
                            const platformData = await platformResponse.json();
                            esAndroid = platformData.plataforma === "Puente";
                            console.log('Plataforma detectada:', esAndroid ? 'Android' : 'PC');
                        } catch (error) {
                            console.log('No se pudo detectar la plataforma, asumiendo PC');
                            esAndroid = false;
                        }

                        if (esAndroid) {
                            // Método Android con reenvío usando x-reenviar-a
                            url = 'http://localhost:8000';
                            headers['x-reenviar-a'] = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                            console.log('Usando método Android con reenvío');

                            // Enviar solicitud Android
                            const http = await fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(opts),
                                headers: headers
                            });

                            const res = await http.json();

                            if (res.ok) {
                                console.log('Impresión Android exitosa');
                                if (typeof ToastMessage !== 'undefined') {
                                    ToastMessage.fire({
                                        text: 'Documento enviado a impresión correctamente (Android)'
                                    });
                                }
                            } else {
                                throw new Error(res.message || 'Error en impresión Android');
                            }

                        } else {
                            // Método PC: intentar local primero, si falla usar reenvío
                            let impresionExitosa = false;

                            try {
                                console.log('Intentando impresión local...');
                                // Intentar impresión local directa
                                const localResponse = await fetch('http://localhost:8000/imprimir', {
                                    method: 'POST',
                                    body: JSON.stringify(opts),
                                    headers: {
                                        'Content-Type': 'application/json; charset=utf-8'
                                    }
                                });

                                const localRes = await localResponse.json();

                                if (localRes.ok) {
                                    console.log('Impresión local exitosa');
                                    if (typeof ToastMessage !== 'undefined') {
                                        ToastMessage.fire({
                                            text: 'Documento enviado a impresión correctamente (Local)'
                                        });
                                    }
                                    impresionExitosa = true;
                                } else {
                                    throw new Error('Impresión local falló: ' + localRes.message);
                                }

                            } catch (errorLocal) {
                                console.log('Error en impresión local:', errorLocal.message);
                                console.log('Intentando impresión remota...');

                                try {
                                    // Usar el método de reenvío remoto
                                    const rutaRemota = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                                    const payload = {
                                        operaciones: opts.operaciones,
                                        nombreImpresora: opts.nombreImpresora,
                                        serial: opts.serial,
                                    };

                                    const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                        method: 'POST',
                                        body: JSON.stringify(payload),
                                        headers: {
                                            'Content-Type': 'application/json; charset=utf-8'
                                        }
                                    });

                                    const remoteRes = await remoteResponse.json();

                                    if (remoteRes.ok) {
                                        console.log('Impresión remota exitosa');
                                        if (typeof ToastMessage !== 'undefined') {
                                            ToastMessage.fire({
                                                text: 'Documento enviado a impresión correctamente (Remoto)'
                                            });
                                        }
                                        impresionExitosa = true;
                                    } else {
                                        throw new Error('Impresión remota falló: ' + remoteRes.message);
                                    }

                                } catch (errorRemoto) {
                                    console.log('Error en impresión remota:', errorRemoto.message);
                                    throw new Error('Falló tanto la impresión local como la remota');
                                }
                            }

                            if (!impresionExitosa) {
                                throw new Error('No se pudo completar la impresión');
                            }
                        }

                    } catch (error) {
                        console.error('Error en el proceso de impresión:', error);

                        // Mostrar error específico según el tipo
                        let errorMessage = 'Error desconocido';

                        if (error.name === 'TypeError' && error.message.includes('fetch')) {
                            errorMessage = 'No se pudo conectar con el servicio de impresión. Verifica que esté funcionando.';
                        } else if (error.message.includes('timeout')) {
                            errorMessage = 'Timeout: El servicio de impresión no responde.';
                        } else if (error.message.includes('HTTP Error')) {
                            errorMessage = `Error de servidor: ${error.message}`;
                        } else {
                            errorMessage = error.message;
                        }

                        if (typeof ToastError !== 'undefined') {
                            ToastError.fire({
                                text: `Error al imprimir: ${errorMessage}`
                            });
                        }
                    }
                

				} else {
					//ToastError.fire({ text: response.error });
				}
			},
			error: function(err) {
				console.log('Ocurrió un error');
			}
		});
	}

    function preaccount(showModal = true) {
		var order_id = currentOrderId;
		$.ajax({
			url: '{{ route('orders.preaccount') }}',
			method: 'post',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
			data: {
				order_id
			},
			success: async function(response) {
				if (response.status) {
                    var table = response.table;
                    var details = response.details;

                    const opts = {
                        serial: serial,
                        nombreImpresora: 'Ticketera',
                        operaciones: [{
                                nombre: 'Iniciar',
                                argumentos: []
                            },
                            {
                                nombre: "EstablecerAlineacion",
                                argumentos: [1]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['PRECUENTA\n']
                            },
                            {
                                nombre: "EstablecerAlineacion",
                                argumentos: [0]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: [`FECHA: ${(new Date()).toLocaleDateString('es-PE')} ${(new Date()).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })}\n`]
                            },
                            {
                                nombre: 'TextoSegunPaginaDeCodigos',
                                argumentos: [
                                    2,
                                    'cp850',
                                    `MESA: ${table}\n`
                                ]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'EstablecerEnfatizado',
                                argumentos: [true]
                            }
                        ]
                    };

                    details.forEach(function(order) {
                        opts.operaciones.push({
                            nombre: 'TextoSegunPaginaDeCodigos',
                            argumentos: [
                                2,
                                'cp850',
                                `${order.cantidad}    ${order.product.nombre}\n`
                            ]
                        }, );
                    });

                    opts.operaciones.push({
                        nombre: 'Corte',
                        argumentos: [1]
                    });

                    try {
                        // IP de la PC que tiene la impresora (cámbiala por la tuya)
                        const IP_PC_IMPRESORA = '192.168.18.46';

                        let url;
                        let headers = {
                            'Content-Type': 'application/json; charset=utf-8'
                        };

                        // Verificar si estamos en Android o PC
                        let esAndroid = false;
                        try {
                            const platformResponse = await fetch('http://localhost:8000/version', {
                                timeout: 3000 // Timeout de 3 segundos
                            });
                            const platformData = await platformResponse.json();
                            esAndroid = platformData.plataforma === "Puente";
                            console.log('Plataforma detectada:', esAndroid ? 'Android' : 'PC');
                        } catch (error) {
                            console.log('No se pudo detectar la plataforma, asumiendo PC');
                            esAndroid = false;
                        }

                        if (esAndroid) {
                            // Método Android con reenvío usando x-reenviar-a
                            url = 'http://localhost:8000';
                            headers['x-reenviar-a'] = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                            console.log('Usando método Android con reenvío');

                            // Enviar solicitud Android
                            const http = await fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(opts),
                                headers: headers
                            });

                            const res = await http.json();

                            if (res.ok) {
                                console.log('Impresión Android exitosa');
                                if (typeof ToastMessage !== 'undefined') {
                                    ToastMessage.fire({
                                        text: 'Documento enviado a impresión correctamente (Android)'
                                    });
                                }
                            } else {
                                throw new Error(res.message || 'Error en impresión Android');
                            }

                        } else {
                            // Método PC: intentar local primero, si falla usar reenvío
                            let impresionExitosa = false;

                            try {
                                console.log('Intentando impresión local...');
                                // Intentar impresión local directa
                                const localResponse = await fetch('http://localhost:8000/imprimir', {
                                    method: 'POST',
                                    body: JSON.stringify(opts),
                                    headers: {
                                        'Content-Type': 'application/json; charset=utf-8'
                                    }
                                });

                                const localRes = await localResponse.json();

                                if (localRes.ok) {
                                    console.log('Impresión local exitosa');
                                    if (typeof ToastMessage !== 'undefined') {
                                        ToastMessage.fire({
                                            text: 'Documento enviado a impresión correctamente (Local)'
                                        });
                                    }
                                    impresionExitosa = true;
                                } else {
                                    throw new Error('Impresión local falló: ' + localRes.message);
                                }

                            } catch (errorLocal) {
                                console.log('Error en impresión local:', errorLocal.message);
                                console.log('Intentando impresión remota...');

                                try {
                                    // Usar el método de reenvío remoto
                                    const rutaRemota = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                                    const payload = {
                                        operaciones: opts.operaciones,
                                        nombreImpresora: opts.nombreImpresora,
                                        serial: opts.serial,
                                    };

                                    const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                        method: 'POST',
                                        body: JSON.stringify(payload),
                                        headers: {
                                            'Content-Type': 'application/json; charset=utf-8'
                                        }
                                    });

                                    const remoteRes = await remoteResponse.json();

                                    if (remoteRes.ok) {
                                        console.log('Impresión remota exitosa');
                                        if (typeof ToastMessage !== 'undefined') {
                                            ToastMessage.fire({
                                                text: 'Documento enviado a impresión correctamente (Remoto)'
                                            });
                                        }
                                        impresionExitosa = true;
                                    } else {
                                        throw new Error('Impresión remota falló: ' + remoteRes.message);
                                    }

                                } catch (errorRemoto) {
                                    console.log('Error en impresión remota:', errorRemoto.message);
                                    throw new Error('Falló tanto la impresión local como la remota');
                                }
                            }

                            if (!impresionExitosa) {
                                throw new Error('No se pudo completar la impresión');
                            }
                        }

                    } catch (error) {
                        console.error('Error en el proceso de impresión:', error);

                        // Mostrar error específico según el tipo
                        let errorMessage = 'Error desconocido';

                        if (error.name === 'TypeError' && error.message.includes('fetch')) {
                            errorMessage = 'No se pudo conectar con el servicio de impresión. Verifica que esté funcionando.';
                        } else if (error.message.includes('timeout')) {
                            errorMessage = 'Timeout: El servicio de impresión no responde.';
                        } else if (error.message.includes('HTTP Error')) {
                            errorMessage = `Error de servidor: ${error.message}`;
                        } else {
                            errorMessage = error.message;
                        }

                        if (typeof ToastError !== 'undefined') {
                            ToastError.fire({
                                text: `Error al imprimir: ${errorMessage}`
                            });
                        }
                    }
                

				} else {
					//ToastError.fire({ text: response.error });
				}
			},
			error: function(err) {
				console.log('Ocurrió un error');
			}
		});
	}

    function mostrarIconoConfirmado() {
        // Selecciona todos los elementos con la clase 'subtotal-container'
        const elementos = document.querySelectorAll('.subtotal-container');
        elementos.forEach(el => {
            // Solo agrega el icono si no existe ya en el elemento
            if (!el.querySelector('.bi-check2-square')) {
                el.innerHTML += '\n<i class="bi bi-check2-square" title="Confirmado"></i>';
            }
        });
    }
</script>
@endsection
