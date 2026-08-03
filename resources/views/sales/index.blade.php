@extends('template.index')

@section('nav')

<x-nav-sales />

@endsection

@section('content')
@php
$colors = ['btn-primary', 'btn-success', 'btn-info', 'btn-warning', 'btn-danger', 'btn-dark'];
@endphp
<div class="container-fluid content-inner mt-n5 py-0">
    <!-- Card que contiene el formulario y la tabla -->
    <div class="card shadow">
        <!-- Cuerpo del Card -->
        <div class="card-body">
            <form action="{{ route('sales.store') }}" id="guardarVenta" method="post" autocomplete="off">
                <div class="row">
                    <div class="col-xl-4 col-lg-12 order-2 order-lg-1 mt-4 mt-lg-0">
                        <div class="btn-group d-flex justify-content-start mb-4">
                            <input type="hidden" name="comprobante" id="comprobante" value="ticket">
                            <button type="button" class="btn btn-outline-primary me-1" id="btn-boleta"
                                onclick="seleccionarComprobante('boleta', event)">Boleta</button>
                            <button type="button" class="btn btn-outline-success me-1" id="btn-factura"
                                onclick="seleccionarComprobante('factura', event)">Factura</button>
                            <button type="button" class="btn btn-outline-info active me-1" id="btn-ticket"
                                onclick="seleccionarComprobante('ticket', event)">Ticket</button>
                        </div>
                        @csrf
                            <div class="mb-2 row">
                                <label class="col-sm-4 col-form-label text-start"><strong>Dni/Ruc</strong></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-xs" id="document"
                                            name="document" maxlength="11" onkeypress="isNumber(event)">
                                        <button type="button" class="btn btn-primary btn-xs"
                                            onclick="searchAPI('#document','#name','#address')"><i
                                                class="bi bi-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label class="col-form-label text-start"><strong>Cliente</strong></label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control form-control-sm" id="client" name="client">
                                </div>
                            </div>
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                        <div class="d-flex flex-column mb-5 mt-3">
                            <div class="d-flex mb-3">
                                <div class="form-check me-4">
                                    <input class="form-check-input" type="checkbox" value="" id="anticipada">
                                    <label class="form-check-label" for="anticipada">
                                        Venta anticipada
                                    </label>
                                </div>
                            </div>
                            <input hidden type="number" name="type_sale" id="type_sale">
                            <input hidden type="number" name="status" id="status">
                            <div id="grupo-fecha-entrega">
                                <label for="fecha_entrega" class="mb-2"><strong>Fecha de entrega</strong></label>
                                <input type="date" id="fecha_entrega" name="fecha_entrega"
                                    class="form-control form-control-sm mb-4"
                                    onkeydown="return false;" 
                                    onpaste="return false;">
                                <label for="hora_entrega" class="form-label">Hora:</label>
                                <input type="text" class="form-control form-control-sm mb-4" id="hora_entrega" name="hora_entrega">

                                <label class="mb-2"><strong>Teléfono</strong></label>
                                <input type="text" id="telefono" name="telefono"
                                    class="form-control form-control-sm mb-4">
                                <label for="sede_recojo" class="mb-2"><strong>Sede Recojo</strong></label>
                                <select type="select" id="sede_recojo" name="sede_recojo"
                                    class="form-control form-control-sm mb-4">
                                    <option value="">-- Seleccionar sede --</option>
                                    @foreach($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                                <label class="mb-2"><strong>Dirección</strong></label>
                                <input type="text" id="direccion" name="direccion"
                                    class="form-control form-control-sm mb-4">
                                <label class="mb-2"><strong>Foto</strong></label>
                                <input type="file" id="foto-input" name="foto" class="form-control form-control-sm mb-4" accept="image/*">
                            </div>
                            <div id="grupo-delivery">
                                <label class="mb-2"><strong>Referencia</strong></label>
                                <input type="text" id="referencia" name="referencia"
                                    class="form-control form-control-sm mb-4">
                            </div>
                            <label class="mb-2"><strong>Observación</strong></label>
                            <input type="text" id="observacion" name="observacion"
                                class="form-control form-control-sm ">
                        </div>
                        <div class="d-flex flex-column mb-5 mt-3">
                            <label class="mb-2"><strong>Método de Pago</strong></label>
                            <div class="d-flex flex-wrap">
                                @foreach ($paymentMethod as $index => $method)
                                @php
                                $colorClass = $colors[$index % count($colors)];
                                @endphp
                                <button type="button"
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
                        <!-- HTML actualizado - Solo mostrar vuelto para Efectivo -->
                        @foreach ($paymentMethod as $method)
                        <div class="d-flex align-items-center mb-4 d-none" id="campos-{{ $method->nombre }}">
                            <label class="mb-2 me-3"><strong>{{ strlen($method->nombre) > 4 ? strtoupper(substr($method->nombre, 0, 4) . '.') : strtoupper($method->nombre) }}</strong></label>
                            <input hidden type="number" name="medio_pago_id" value="{{ $method->id }}">
                            <div class="input-group me-2">
                                <span class="input-group-text">S/</span>
                                <input type="text" class="form-control" placeholder="Ingrese Monto"
                                    name="monto[{{ $method->id }}]"
                                    onkeypress="isDecimal(event)"
                                    oninput="calcularVueltoEfectivo('{{ $method->nombre }}', '{{ $method->id }}', this)">
                            </div>
                            <!-- Campo de vuelto - SOLO para efectivo -->
                            @if(strtolower($method->nombre) === 'efectivo')
                            <div class="input-group me-2">
                                <input type="text" class="form-control" placeholder="0.00" style="width: 150px;"
                                    id="vuelto-efectivo" readonly>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="col-xl-8 col-lg-12 order-1 order-lg-2">
                        <!-- Seleccionar Productos -->
                        <div class="form-group">
                            <label for="producto_id"
                                class="col-sm-3 col-form-label text-start"><strong>Producto</strong></label>
                            <div class="col-md-12">
                                <input hidden type="number" class="form-control" name="producto_id" id="producto_id">
                                <input type="text" class="form-control" name="name" id="search-product"
                                    placeholder="Buscar Producto">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="producto_id"
                                class="col-sm-3 col-form-label text-start"><strong>Categorías</strong></label>
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
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Botón guardar: SIEMPRE al final -->
                    <div class="col-12 order-3 mt-4 text-end">
                        <h5><strong>TOTAL: S/ <span id="totalAmount" name="total">0.00</span></strong></h5>
                        <h6><strong>SALDO: S/ <span id="saldoAmount">0.00</span></strong></h6>
                        <input hidden type="number" step="0.01" name="total" id="totalAmountInput" value="0">
                        <input hidden type="text" name="fecha" id="fechaInput">
                        <button class="btn btn-success mt-3" type="submit" id="btnGuardar">Guardar</button>
                    </div>
                </div>
            </form>
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
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script>
    var productsSelect = @json($products);
    // const productSitePrices = @json($productSitePrices);
    const userSede = @json($userSede);

    $('#search-product').autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();

            const results = productsSelect
                .filter(p => p.nombre.toLowerCase().includes(term))
                .map(p => {
                    const stock = p.stock_cantidad || 0;
                    return {
                        label: `${p.nombre} (${stock})`,
                        value: p.nombre,
                        id: p.id,
                        stock: stock
                    };
                });

            response(results);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            handleProductClickSelect(ui.item.id, ui.item.value);
            $(this).val('');
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };

    //Database
    var serial = "{{ config('printer.serial') }}";
    let selectedProducts = [];
    let selectedCategory = null;
    let total = 0;
    let vuelto = 0;

    const USER_PIN = "{{ auth()->user()->pin }}";
    console.log('USER_PIN:', USER_PIN);

    let currentProductIndex = -1;

    function isNumber(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;
        if (charCode < 48 || charCode > 57) {
            evt.preventDefault();
            return false;
        }
        return true;
    }

    function isDecimal(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;
        if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
            var input = evt.target || evt.srcElement;
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

    //Api Sunat
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

    const chkAnticipada = document.getElementById('anticipada');
    const chkDelivery = document.getElementById('delivery');
    const grupoFecha = document.getElementById('grupo-fecha-entrega');
    const grupoDel = document.getElementById('grupo-delivery');
    const fechaEnt = document.getElementById('fecha_entrega');
    const direccion = document.getElementById('direccion');
    const referencia = document.getElementById('referencia');
    const nombreCliente = document.getElementById('client')

    function limpiarCamposEntrega() {
        // Fecha de entrega
        const fechaEntrega = document.getElementById('fecha_entrega');
        if (fechaEntrega) fechaEntrega.value = '';

        // Hora de entrega
        const horaEntrega = document.getElementById('hora_entrega');
        if (horaEntrega) horaEntrega.value = '';

        // Teléfono
        const telefono = document.getElementById('telefono');
        if (telefono) telefono.value = '';

        // Sede Recojo (select)
        const sedeRecojo = document.getElementById('sede_recojo');
        if (sedeRecojo) sedeRecojo.selectedIndex = 0;

        // Dirección
        const direccion = document.getElementById('direccion');
        if (direccion) direccion.value = '';

        // Foto (file input)
        const fotoInput = document.getElementById('foto-input');
        if (fotoInput) fotoInput.value = '';
    }

    function actualizarHabilitacion() {
        // Venta anticipada
        const ant = chkAnticipada.checked;
        const del = {{ auth()->user()->hasRole('delivery') ? 'true' : 'false' }}; //chkDelivery.checked;

        grupoFecha.classList.toggle('d-none', !(ant)); // || del));
        fechaEnt.disabled = !(ant); // || del);
        fechaEnt.required = ant; // || del;

        // Mostrar grupoDel solo si delivery está activo
        // grupoDel.classList.toggle('d-none', !del);
        // referencia.disabled = !del;
        // referencia.required = del;

        // Dirección única
        direccion.disabled = !(ant); // || del);
        // direccion.required = ant || del; // Dirección no es requerida

        nombreCliente.required = ant;

        const $status = $('#status');
        const $type_sale = $('#type_sale');

        //directa
        $type_sale.val('0');
        $status.val('0');

        //anticipada (no entregada)
        if (!del && ant) {
            $status.val('1');
            $type_sale.val('1');
        }

        //delivery directa
        if (del && !ant) {
            $status.val('0');
            $type_sale.val('2');
        }

        //delivery anticipada (no entregada)
        if (del && ant) {
            $status.val('1');
            $type_sale.val('3');
        }

        limpiarCamposEntrega();

    }

    const btnBoleta = document.getElementById('btn-boleta');
    const btnFactura = document.getElementById('btn-factura');
    const btnTicket = document.getElementById('btn-ticket');

    chkAnticipada.addEventListener('change', function() {
        actualizarHabilitacion();
    });

    function seleccionarComprobante(comprobante, event) {
        const parent = event.target.closest('.btn-group');
        Array.from(parent.children).forEach(child => {
            child.classList.remove('active');
        });
        event.target.classList.add('active');
        document.getElementById('comprobante').value = comprobante;
    }

    document.addEventListener('DOMContentLoaded', function() {
        actualizarHabilitacion();
        document.getElementById('comprobante').value = "ticket";

        // Para input type="datetime-local"
        const fechaInput = document.getElementById('fechaInput');
        if (fechaInput) {
            const now = new Date();

            // Obtener el año, mes, día, horas y minutos en la zona horaria local
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0'); // Mes comienza en 0, por eso se suma 1
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            // Crear la cadena con el formato correcto (YYYY-MM-DDTHH:mm)
            const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;

            // Asignar el valor al input datetime-local
            fechaInput.value = formattedDate;

            // Mostrar el valor de formattedDate
            console.log('fecha:', formattedDate);
        }
    });

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

                // Mostrar nombre del producto con stock entre paréntesis
                const stock = producto.stock_cantidad || 0;
                productElement.textContent = `${producto.nombre.toUpperCase()} (${stock})`;

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
            existing.cantidad += 1;
        }
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
            existing.cantidad += 1;
        }
        renderProductTable();
        const $input = $('#search-product');
        $input.val('');
    }

    // 4. Modificar la función renderProductTable para mostrar descuentos
    function renderProductTable() {
        const tbody = document.querySelector('tbody');
        const activeElement = document.activeElement;
        let activeInputIndex = null;
        let activeInputType = null;
        let cursorPosition = null;
        let currentValue = null;

        if (activeElement && (activeElement.classList.contains('cantidad-input') || activeElement.classList.contains('precio-input'))) {
            activeInputIndex = activeElement.getAttribute('data-index');
            activeInputType = activeElement.classList.contains('cantidad-input') ? 'cantidad' : 'precio';
            cursorPosition = activeElement.selectionStart;
            currentValue = activeElement.value;
        }

        // const activeElementId = activeElement ? activeElement.id : null;
        tbody.innerHTML = '';

        if (selectedProducts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Seleccione un Producto</td>
                </tr>
            `;
            updateTotal();
            return;
        }

        selectedProducts.forEach((p, index) => {
            // FIX: Asegurar que precio sea un número
            const precio = parseFloat(p.precio) || 0;
            const cantidad = parseFloat(p.cantidad) || 0;
            const subtotal = (precio * cantidad).toFixed(2);

            const row = `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td hidden class="text-center">${p.id}</td>
                    <td class="text-center">${p.nombre}</td>
                    <td class="text-center">
                        <input type="number" id="input-quantity" class="form-control cantidad-input" data-index="${index}"
                            min="1" value="${cantidad}" oninput="updateSubtotal(${index})" 
                            style="width: 100px;">
                    </td>
                    <td class="text-center">
                       <input type="text"
                        inputmode="decimal"
                        class="form-control precio-input"
                        data-index="${index}"
                        value="${precio.toFixed(2)}"
                        oninput="updatePrecio(${index})"
                        style="width: 100px;">
                            
                        ${p.precioOriginal && parseFloat(p.precioOriginal) !== precio 
                            ? `<br><small class="text-muted"><s>S/ ${parseFloat(p.precioOriginal).toFixed(2)}</s></small>` 
                            : ''}
                    </td>

                    <td class="text-center">S/ ${subtotal}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        updateTotal();
        actualizarMontosMetodosPago();

        // if (activeElementId && document.getElementById(activeElementId)) {
        if (activeInputIndex !== null && activeInputType) {
            const selector = `.${activeInputType}-input[data-index="${activeInputIndex}"]`;
            const restoredElement = document.querySelector(selector);
            if (restoredElement) {
                restoredElement.focus();

                // Restaurar el valor original (sin alterar el cursor)
                restoredElement.value = currentValue;

                // Restaurar la posición del cursor
                if (cursorPosition !== null && cursorPosition <= restoredElement.value.length) {
                    restoredElement.setSelectionRange(cursorPosition, cursorPosition);
                }
            }
        }
    }

    function actualizarMontosMetodosPago() {
        const contenedor = document.querySelector('.d-flex.flex-wrap');
        const botonesActivos = contenedor.querySelectorAll('.btn.active');

        if (botonesActivos.length === 1) {
            const botonActivo = botonesActivos[0];
            const camposId = botonActivo.dataset.campos;
            const idActivo = botonActivo.dataset.id;
            const total = $('#totalAmount').text().trim();

            // Actualiza el input monto con el total
            $(`#${camposId} input[name="monto[${idActivo}]"]`).val(total);
        } else {
            botonesActivos.forEach(boton => {
                const camposId = boton.dataset.campos;
                $(`#${camposId} input[type="text"]`).val('');
            });
        }

        // Actualizar saldo después de cambiar montos
        calcularSaldo();
    }


    function updateTotal() {
        totalS = selectedProducts.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);


        let total = totalS;

        $('#totalAmount').text(total.toFixed(2));
        $('#totalAmountInput').val(total.toFixed(2));

        // Actualizar el saldo cuando cambie el total
        calcularSaldo();
    }

    function removeItem(index) {
        selectedProducts.splice(index, 1);
        renderProductTable();
    }

    function updateSubtotal(index) {
        const inputs = document.querySelectorAll('#input-quantity');
        const newQuantity = parseFloat(inputs[index].value);

        if (!isNaN(newQuantity) && newQuantity > 0) {
            selectedProducts[index].cantidad = newQuantity;

            // Actualizar solo el subtotal de esta fila
            updateRowSubtotal(index);

            // Solo actualizar totales sin re-renderizar toda la tabla
            updateTotal();
            actualizarMontosMetodosPago();
        }
    }

    function updatePrecio(index) {
        const inputs = document.querySelectorAll('.precio-input');
        let valor = inputs[index].value;

        // Reemplaza la coma por punto si existe
        valor = valor.replace(',', '.');

        const nuevoPrecio = parseFloat(valor);

        if (!isNaN(nuevoPrecio) && nuevoPrecio >= 0) {
            selectedProducts[index].precio = nuevoPrecio;

            // Actualizar solo el subtotal de esta fila
            updateRowSubtotal(index);

            // Solo actualizar totales sin re-renderizar toda la tabla
            updateTotal();
            actualizarMontosMetodosPago();
        }
    }

    function updateRowSubtotal(index) {
        const producto = selectedProducts[index];
        const precio = parseFloat(producto.precio) || 0;
        const cantidad = parseFloat(producto.cantidad) || 0;
        const subtotal = (precio * cantidad).toFixed(2);

        // Buscar la fila correspondiente y actualizar solo la celda del subtotal
        const tbody = document.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        if (rows[index]) {
            const subtotalCell = rows[index].cells[5]; // La celda del subtotal es la 6ta (índice 5)
            if (subtotalCell) {
                subtotalCell.textContent = `S/ ${subtotal}`;
            }
        }
    }



    function seleccionarMedioPago(medio_id, event) {
        const btn = event.target;
        btn.classList.toggle('active');

        const campos = btn.dataset.campos;

        $(`#${campos}`).toggleClass('d-none');
        $(`#${campos} input[name="monto[${medio_id}]"]`).val('');

        // Actualizar los inputs según métodos activos y total
        actualizarMontosMetodosPago();

        // Actualizar saldo cuando se activa/desactiva un método de pago
        calcularSaldo();

        if (!btn.classList.contains('active')) {
            btn.blur();
        }
    }

    function calcularVueltoTotal() {
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const totalVenta = parseFloat($('#totalAmount').text()) || 0;
        const campoVuelto = document.getElementById('vuelto-efectivo');

        if (!campoVuelto) {
            return;
        }

        let totalPagado = 0;
        let hayEfectivo = false;
        let inputEfectivo = null;
        let montoEfectivo = 0;

        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const nombreMetodo = camposId.replace('campos-', '');
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;

            if (nombreMetodo.toLowerCase() === 'efectivo') {
                hayEfectivo = true;
                inputEfectivo = inputMonto;
                montoEfectivo = monto;
            }

            totalPagado += monto;
        });

        if (hayEfectivo && totalPagado > totalVenta) {
            const vueltoCalculado = totalPagado - totalVenta;
            
            // VALIDACIÓN CRÍTICA: El vuelto no puede exceder el monto de efectivo
            if (vueltoCalculado > montoEfectivo) {
                // El vuelto excede el efectivo disponible - esto es imposible
                campoVuelto.value = '0.00';
                if (inputEfectivo) {
                    inputEfectivo.removeAttribute('data-monto-real');
                }
                
                // Mostrar advertencia al usuario
                ToastMessage.fire({
                    icon: 'warning',
                    text: `El vuelto calculado (S/ ${vueltoCalculado.toFixed(2)}) excede el efectivo ingresado (S/ ${montoEfectivo.toFixed(2)}). Ajuste los montos.`
                });
            } else {
                // Vuelto válido - no excede el efectivo
                const vuelto = vueltoCalculado;
                campoVuelto.value = vuelto.toFixed(2);
                
                // CORRECCIÓN: Ajustar el monto del efectivo al valor real que se debe registrar
                if (inputEfectivo && botonesActivos.length === 1) {
                    // Si solo hay efectivo, el monto debe ser exactamente el total de la venta
                    const montoEfectivoReal = totalVenta;
                    inputEfectivo.setAttribute('data-monto-real', montoEfectivoReal.toFixed(2));
                } else if (inputEfectivo && botonesActivos.length > 1) {
                    // Si hay múltiples métodos, calcular cuánto corresponde realmente al efectivo
                    let otrosPagos = 0;
                    botonesActivos.forEach(boton => {
                        const camposId = boton.dataset.campos;
                        const nombreMetodo = camposId.replace('campos-', '');
                        if (nombreMetodo.toLowerCase() !== 'efectivo') {
                            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
                            otrosPagos += parseFloat(inputMonto?.value) || 0;
                        }
                    });
                    const montoEfectivoReal = totalVenta - otrosPagos;
                    inputEfectivo.setAttribute('data-monto-real', Math.max(0, montoEfectivoReal).toFixed(2));
                }
            }
        } else {
            campoVuelto.value = '0.00';
            if (inputEfectivo) {
                inputEfectivo.removeAttribute('data-monto-real');
            }
        }

        // Calcular y mostrar el saldo
        calcularSaldo();
    }

    function calcularVueltoEfectivo(nombreMetodo, idMetodo, inputElement) {
        // Solo calcular para efectivo
        if (nombreMetodo.toLowerCase() !== 'efectivo') {
            calcularSaldo();
            return;
        }

        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const totalVenta = parseFloat($('#totalAmount').text()) || 0;
        const campoVuelto = document.getElementById('vuelto-efectivo');

        if (!campoVuelto) {
            calcularSaldo();
            return;
        }

        let totalPagado = 0;
        let montoEfectivo = parseFloat(inputElement.value) || 0;

        // Calcular total pagado con todos los métodos activos
        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;
            totalPagado += monto;
        });

        // Calcular vuelto solo si se paga más del total
        if (totalPagado > totalVenta) {
            const vueltoCalculado = totalPagado - totalVenta;
            
            // Validar que el vuelto no exceda el efectivo ingresado
            if (vueltoCalculado <= montoEfectivo) {
                campoVuelto.value = vueltoCalculado.toFixed(2);
                
                // Guardar el monto real de efectivo (lo que realmente se queda)
                const montoEfectivoReal = montoEfectivo - vueltoCalculado;
                inputElement.setAttribute('data-monto-real', montoEfectivoReal.toFixed(2));
            } else {
                // El vuelto excede el efectivo - resetear
                campoVuelto.value = '0.00';
                inputElement.removeAttribute('data-monto-real');
            }
        } else {
            campoVuelto.value = '0.00';
            inputElement.removeAttribute('data-monto-real');
        }

        calcularSaldo();
    }

    function calcularSaldo() {
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const totalVenta = parseFloat($('#totalAmount').text()) || 0;
        let totalPagado = 0;
        let hayEfectivo = false;

        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const nombreMetodo = camposId.replace('campos-', '');
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;
            
            if (nombreMetodo.toLowerCase() === 'efectivo') {
                hayEfectivo = true;
            }
            
            totalPagado += monto;
        });

        const saldo = totalVenta - totalPagado;
        const saldoElement = $('#saldoAmount');
        const saldoParent = saldoElement.parent();

        // Mostrar el saldo
        if (saldo > 0) {
            // Falta dinero por pagar
            saldoElement.text(saldo.toFixed(2));
            saldoParent.removeClass('text-success text-warning').addClass('text-danger');
            saldoParent.find('strong').text('FALTA: S/ ');
        } else if (saldo < 0) {
            // Se está pagando de más
            const exceso = Math.abs(saldo);
            saldoElement.text(exceso.toFixed(2));
            
            if (hayEfectivo) {
                saldoParent.removeClass('text-danger text-warning').addClass('text-success');
                saldoParent.find('strong').text('VUELTO: S/ ');
            } else {
                saldoParent.removeClass('text-success text-danger').addClass('text-warning');
                saldoParent.find('strong').text('EXCESO: S/ ');
            }
        } else {
            // Pago exacto
            saldoElement.text('0.00');
            saldoParent.removeClass('text-danger text-warning').addClass('text-success');
            saldoParent.find('strong').text('PAGADO: S/ ');
        }
    }

    function limpiarMedioPago() {
        // Limpiar todos los métodos de pago activos
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active');
        botonesActivos.forEach(boton => {
            boton.classList.remove('active');
            const camposId = boton.dataset.campos;
            $(`#${camposId}`).addClass('d-none');
            $(`#${camposId} input[type="text"]`).val('');
        });
        
        // Limpiar vuelto
        const campoVuelto = document.getElementById('vuelto-efectivo');
        if (campoVuelto) {
            campoVuelto.value = '';
        }
        
        calcularSaldo();
    }

    $('#guardarVenta').on('submit', function(e) {
        e.preventDefault();

        const botonesMedioPago = document.querySelectorAll('.d-flex.flex-wrap button');
        const metodoPagoSeleccionado = Array.from(botonesMedioPago).some(btn => btn.classList.contains('active'));
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');

        if (!metodoPagoSeleccionado) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe seleccionar al menos un método de pago.'
            });
            return;
        }

        // Validar que todos los métodos activos tengan monto
        if (botonesActivos.length > 1) {
            let algunVacio = false;
            botonesActivos.forEach(boton => {
                const camposId = boton.dataset.campos;
                const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
                const monto = parseFloat(inputMonto?.value) || 0;
                if (monto <= 0) {
                    algunVacio = true;
                }
            });
            if (algunVacio) {
                ToastMessage.fire({
                    icon: 'error',
                    text: 'Debe ingresar un monto mayor a cero en todos los métodos de pago seleccionados.'
                });
                return;
            }
        }

        const esAnticipada = document.getElementById('anticipada').checked;
        const tipoComprobante = document.getElementById('comprobante').value;
        const documentValue = document.getElementById('document').value.trim();

        // VALIDACIÓN DE DOCUMENTO PARA FACTURA
        if (tipoComprobante === "factura" && !documentValue) {
            ToastError.fire({
                text: 'Debe ingresar un RUC válido para emitir factura'
            });
            return;
        }

        // Calcular total sumando los productos seleccionados
        const totalVenta = selectedProducts
            .reduce((sum, prod) => sum + ((parseFloat(prod.precio) || 0) * (parseFloat(prod.cantidad) || 0)), 0);

        if (typeof totalVenta === 'undefined' || isNaN(totalVenta) || totalVenta <= 0) {
            ToastMessage.fire({
                icon: 'error',
                text: 'No es posible registrar una venta con total S/ 0.00. Verifique los productos y montos asignados.'
            });
            return;
        }

        // CALCULAR PAGOS Y VALIDACIONES
        let totalPagado = 0;
        let hayEfectivo = false;
        let montoEfectivo = 0;
        let metodosNoEfectivo = 0;

        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const nombreMetodo = camposId.replace('campos-', '');
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;
            
            if (nombreMetodo.toLowerCase() === 'efectivo') {
                hayEfectivo = true;
                montoEfectivo = monto;
            } else {
                metodosNoEfectivo += monto;
            }
            
            totalPagado += monto;
        });

        const saldo = totalVenta - totalPagado;

        console.log('=== VALIDACIONES DE PAGO ===');
        console.log('Total venta:', totalVenta);
        console.log('Total pagado:', totalPagado);
        console.log('Métodos no efectivo:', metodosNoEfectivo);
        console.log('Monto efectivo:', montoEfectivo);
        console.log('Saldo:', saldo);
        console.log('Es anticipada:', esAnticipada);

        // VALIDACIONES PRINCIPALES
        if (!esAnticipada) {
            // VENTA DIRECTA: Debe estar completamente pagada
            
            // Si solo hay métodos no efectivo, el pago debe ser exacto
            if (!hayEfectivo && Math.abs(saldo) > 0.01) {
                ToastMessage.fire({
                    icon: 'error',
                    text: saldo > 0 
                        ? `Falta pagar S/ ${saldo.toFixed(2)}. Sin efectivo debe pagar el monto exacto.`
                        : `Exceso de S/ ${Math.abs(saldo).toFixed(2)}. Sin efectivo no puede pagar de más.`
                });
                return;
            }
            
            // Si hay efectivo, validar que el vuelto no exceda el efectivo ingresado
            if (hayEfectivo && saldo < 0) {
                const vuelto = Math.abs(saldo);
                if (vuelto > montoEfectivo) {
                    ToastMessage.fire({
                        icon: 'error',
                        text: `El vuelto (S/ ${vuelto.toFixed(2)}) excede el efectivo recibido (S/ ${montoEfectivo.toFixed(2)})`
                    });
                    return;
                }
            }
            
            // Si hay efectivo pero falta dinero
            if (hayEfectivo && saldo > 0.01) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Para venta directa debe pagar el monto completo. Falta: S/ ${saldo.toFixed(2)}`
                });
                return;
            }
            
        } else {
            // VENTA ANTICIPADA: Puede tener saldo pendiente
            
            // Para boleta/factura debe pagar completo
            if ((tipoComprobante === 'boleta' || tipoComprobante === 'factura') && saldo > 0) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Para ventas anticipadas con ${tipoComprobante} debe pagar el monto completo. Falta: S/ ${saldo.toFixed(2)}`
                });
                return;
            }
            
            // No puede exceder el total sin efectivo
            if (!hayEfectivo && saldo < 0) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Sin efectivo no puede pagar más del total. Exceso: S/ ${Math.abs(saldo).toFixed(2)}`
                });
                return;
            }
            
            // Si hay efectivo y exceso, validar vuelto
            if (hayEfectivo && saldo < 0) {
                const vuelto = Math.abs(saldo);
                if (vuelto > montoEfectivo) {
                    ToastMessage.fire({
                        icon: 'error',
                        text: `El vuelto (S/ ${vuelto.toFixed(2)}) excede el efectivo recibido (S/ ${montoEfectivo.toFixed(2)})`
                    });
                    return;
                }
            }
        }

        // Si llegó aquí, todas las validaciones pasaron
        console.log('✅ Validaciones aprobadas, procesando venta...');

        const form = this;
        const formData = new FormData(form);

        // Limpiar datos de pago existentes
        for (let key of formData.keys()) {
            if (key.startsWith('monto[')) {
                formData.delete(key);
            }
        }
        
        // Agregar los montos correctos
        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const idMetodo = boton.dataset.id;
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            
            if (inputMonto) {
                const montoReal = inputMonto.getAttribute('data-monto-real');
                const montoFinal = montoReal || inputMonto.value;
                
                if (parseFloat(montoFinal) > 0) {
                    formData.append(`monto[${idMetodo}]`, montoFinal);
                }
            }
        });

        formData.append('products', JSON.stringify(selectedProducts));
        const voucherType = document.getElementById('comprobante').value;
        const voucherTypeFormatted = voucherType.charAt(0).toUpperCase() + voucherType.slice(1).toLowerCase();
        formData.append('voucher_type', voucherTypeFormatted);

        if (document.getElementById('anticipada').checked) {
            formData.append('anticipada', 'on');
        }

        // Función para resetear formulario
        const resetFormulario = () => {
            $('#guardarVenta')[0].reset();
            selectedProducts = [];
            renderProductTable();
            document.getElementById('anticipada').checked = false;
            document.getElementById('foto-input').value = '';
            document.getElementById('anticipada').dispatchEvent(new Event('change'));

            const fechaInput = document.getElementById('fechaInput');
            if (fechaInput) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
                fechaInput.value = formattedDate;
                console.log('reset fecha:', formattedDate);
            }
            $('#vuelto').addClass('d-none');

            //seleccionar ticket y guardar metodo de pago
            limpiarMedioPago();
        };


        // ============= FUNCIÓN PRINCIPAL DE GUARDADO =============
        const handleSuccess = async (response) => {
            

            // console.log("=== RESPUESTA DEL BACKEND ===");
            // console.log("Response completo:", response);
            // console.log("============================");

            if (response.status) {
                
                
                // 1. MOSTRAR MENSAJE DE ÉXITO
                ToastMessage.fire({
                    icon: 'success',
                    text: '✅ Venta registrada correctamente.'
                });

                @if(auth()->user()->hasRole('delivery'))
                    // 2. ABRIR EL PDF
                    if (response.sale_id) {
                        resetFormulario();
                        // Obtener el tipo de comprobante real de la respuesta
                        let tipoComprobante = response.voucher_type || document.getElementById('comprobante').value;
                        tipoComprobante = tipoComprobante.toLowerCase();

                        let pdfUrl = '';
                        if (tipoComprobante === 'ticket') {
                            pdfUrl = @json(route('sales.pdf_detallado', ['sale' => ':id']));
                        } else {
                            pdfUrl = @json(route('sales.pdf', ['sale' => ':id']));
                        }
                        pdfUrl = pdfUrl.replace(':id', response.sale_id);
                        window.open(pdfUrl, '_blank');
                    }
                @endif


                document.getElementById('btn-boleta').classList.remove('active');
                document.getElementById('btn-factura').classList.remove('active');
                document.getElementById('btn-ticket').classList.add('active');

                // Asignar el valor al input oculto
                document.getElementById('comprobante').value = 'ticket';

                imprimirVenta(response.sale_id); //esto recarga la pág

                // spinner.classList.add('spinner-hidden');
                // spinner.classList.remove('spinner-visible');
            } else {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');
                ToastError.fire({
                    text: 'No se pudo registrar la venta'
                });
            }
        };

        // Mostrar spinner mientras se procesa
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        // ENVIAR VENTA AL SERVIDOR
        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: handleSuccess,
            error: function() {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');
                ToastError.fire({
                    text: 'Error al registrar venta'
                });
            }
        });
    });


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
                        // Divide la dirección en líneas de máximo 40 caracteres
                        let direccion = `DIRECCION: ${venta.direccion}`;
                        while (direccion.length > 40) {
                            operaciones.push({
                                nombre: "EscribirTexto",
                                argumentos: [direccion.substring(0, 40) + '\n']
                            });
                            direccion = direccion.substring(40);
                        }
                        if (direccion.length > 0) {
                            operaciones.push({
                                nombre: "EscribirTexto",
                                argumentos: [direccion + '\n']
                            });
                        }
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
                            location.reload();
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
                                location.reload();
                            } else {
                                throw new Error('Impresión remota falló: ' + remoteRes.message);
                            }
                        } catch (errorRemoto) {
                            console.error('Error al imprimir boleta/factura:', errorRemoto);
                            ToastError.fire({
                                text: 'Error al imprimir la boleta/factura: ' + errorRemoto.message
                            });
                            return;
                        } finally {
                            location.reload();
                        }
                    } finally {
                        location.reload();
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

                if (venta.type_sale == 1) {
                    if (venta.fecha_entrega) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`FECHA ENTREGA: ${venta.fecha_entrega}\n`]
                        });
                    }

                    if (venta.hora_entrega) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`HORA ENTREGA: ${venta.hora_entrega}\n`]
                        });
                    }

                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`CLIENTE: ${venta.cliente}\n`]
                    });

                    if (venta.telefono) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`TELEFONO: ${venta.telefono}\n`]
                        });
                    }

                    if (venta.sede_recojo) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`SEDE RECOJO: ${venta.sede_recojo}\n`]
                        });
                    }
                }

                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });

                if (venta.type_sale == 1) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: ['INFORMACION DE PAGOS:\n']
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: [`TOTAL VENTA: S/${venta.total}\n`]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: [`TOTAL PAGADO: S/${(venta.total - venta.saldo).toFixed(2)}\n`]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: [`SALDO PENDIENTE: S/${venta.saldo}\n`]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: ['----------------------------------------\n']
                    });
                }

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

                if (venta.type_sale == 1) {
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
                        location.reload();
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
                            location.reload();
                        } else {
                            throw new Error('Impresión remota falló: ' + remoteRes.message);
                        }
                    } catch (errorRemoto) {
                        console.error('Error al imprimir ticket:', errorRemoto);
                        ToastError.fire({
                            text: 'Error al imprimir el ticket: ' + errorRemoto.message
                        });
                    } finally{
                        location.reload();
                    }
                } finally{
                    location.reload();
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
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('tbody');

  // Guarda estado por campo (clave = "tipo-index")
  const editingState = new Map();
  const isTarget = el => el && (el.classList.contains('cantidad-input') || el.classList.contains('precio-input'));
  const keyFor = el => (el.classList.contains('cantidad-input') ? 'cantidad' : 'precio') + '-' + el.dataset.index;

  // Al enfocar: guardar valor original y limpiar
  tbody.addEventListener('focusin', (e) => {
    const el = e.target;
    if (!isTarget(el)) return;
    const key = keyFor(el);
    editingState.set(key, { original: el.value, edited: false });
    el.value = '';                 // limpia para que el usuario escriba
    el.select?.();                 // opcional: selecciona para sobrescribir
  });

  // Si el usuario escribe, marcamos como editado
  tbody.addEventListener('input', (e) => {
    const el = e.target;
    if (!isTarget(el)) return;
    const st = editingState.get(keyFor(el));
    if (st) st.edited = true;
  });

  // Al salir: si quedó vacío o no editó, restaurar el valor original
  tbody.addEventListener('focusout', (e) => {
    const el = e.target;
    if (!isTarget(el)) return;
    const key = keyFor(el);
    const st = editingState.get(key);
    if (!st) return;

    if (el.value.trim() === '' || !st.edited) {
      el.value = st.original;

      // dispara tus actualizaciones si corresponde
      const idx = el.dataset.index;
      if (el.classList.contains('cantidad-input')) {
        if (typeof updateSubtotal === 'function') updateSubtotal(idx);
      } else {
        if (typeof updatePrecio === 'function') updatePrecio(idx);
      }
    }
    editingState.delete(key);
  });
});
</script>

<style scoped>
    .table-total,
    th,
    td {
        border: none !important;
    }

    .cantidad-input {
        width: 60px;
        padding: 4px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        text-align: center;
    }
</style>
@endsection