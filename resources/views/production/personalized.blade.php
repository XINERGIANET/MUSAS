@extends('template.index')

@php
    $storeRoute = Route::currentRouteName() === 'production.personalized'
        ? route('production.storePersonalized')
        : (Route::currentRouteName() === 'production.delivery'
            ? route('production.storeDelivery')
            : route('production.storePersonalized')); // valor por defecto

    $historicoRoute =  Route::currentRouteName() === 'production.personalized'
        ? route('production.historico')
        : (Route::currentRouteName() === 'production.delivery'
            ? route('production.historicoDelivery')
            : route('production.historico')); // valor por defecto
@endphp

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary" href="{{ url()->current() }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary active" href="{{ $historicoRoute }}">Historico</a>
    </li>
</ul>
@endsection

@section('styles')
<style>
    #btnImportar {
        margin: 0 10px !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Para mejor scroll en móviles */
    }

    #productionTable {
        min-width: 1200px; /* Ancho mínimo para forzar scroll horizontal */
        white-space: nowrap; /* Evita que el contenido se ajuste */
    }

    #productionTable th:nth-child(5) { min-width: 100px; } /* Precio Unitario */
    #productionTable th:nth-child(6) { min-width: 100px; } /* Cantidad */
    #productionTable th:nth-child(7) { min-width: 180px; } /* Subtotal */
    #productionTable th:nth-child(8) { min-width: 200px; } /* Subtotal */

    #productionTable td:nth-child(5) { min-width: 100px; }
    #productionTable td:nth-child(6) { min-width: 100px; }
    #productionTable td:nth-child(7) { min-width: 180px; }
    #productionTable th:nth-child(8) { min-width: 200px; } /* Subtotal */

    /* Inputs dentro de la tabla */
    #productionTable .form-control {
        width: 100%;
        min-width: auto;
    }

    /* Select dentro de la tabla */
    #productionTable select.form-control {
        min-width: 140px;
    }

    .cantidad-input {
        width: 100px;
    }
</style>
@endsection

@section('header')
    <h2>Producción</h2>
    <p>{{ $title }}</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">

                        <!-- Formulario principal de datos de la compra -->
                        <form id="productionForm">
                            @csrf
                            <div class="row">
                                <label class="col-sm-3 col-form-label text-start">Buscar Producto:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="search-product" name="search-product" placeholder="Buscar producto...">
                                </div>
                            </div>

                            <div id="nuevoproducto" style="display: none;">
                                <div class="row align-items-end mb-4">
                                    <div class="col-md-3">
                                        <label for="category_id" class="form-label">Categoría <!-- <span class="text-danger">*</span> --></label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto">
                                    </div>

                                    <div class="col-md-3">
                                        <select class="form-control" id="unidad_medida" name="unidad_medida">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-secondary" id="saveProduct">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <hr style="border: none; border-top: 2px solid #888; margin: 20px 0;">

                            <div class="col-sm-12 mb-3">
                                
                                <div class="row align-items-end">
                                    <!-- Total y botón al final -->
                                    <div class="col-md-12 text-end">
                                        <div class="mb-2">
                                            <strong>Total: S/ <span id="totalAmount">0.00</span></strong>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="saveProduction">
                                            Guardar producción
                                        </button>
                                    </div>
                                </div>
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
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </form>
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

<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script>
 
    function collectTableData() {
        const products = [];
        
        $('#productionTable tbody tr').each(function() {
            const row = $(this);
            const productId = row.data('product-id');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const subtotal = parseFloat(row.find('.subtotal').val()) || 0;

            if (quantity > 0) {
                const productData = {
                    product_id: productId,
                    quantity: quantity,
                    price: price,
                    subtotal: subtotal
                };

                // Si es un producto nuevo (ID negativo), agregar datos adicionales
                if (productId < 0) {
                    productData.category_id = row.data('category-id');
                    productData.nombre = row.data('nombre');
                    productData.unidad_medida = row.data('unidad-medida');
                }

                products.push(productData);
            }
        });

        return products;
    }

    var selectedProducts = [];

    var newproducts = @json($products);

    $('#search-product').autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();

            const results = newproducts
                .filter(p => p.nombre.toLowerCase().includes(term))
                .map(p => ({
                    label: p.nombre,
                    value: p.nombre,
                    id: p.id,
                }));

            response(results);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            handleProductClickSelect(ui.item.id);
            $(this).val('');
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };


    function handleProductClickSelect(productId) {
        // Buscar el producto en la lista
        const selectedProduct = newproducts.find(p => p.id === productId);

        if (!selectedProduct) return;

        // Verificar si ya existe en la tabla
        const existingRow = $(`#productionTable tr[data-product-id="${productId}"]`);

        if (existingRow.length > 0) {
            // Si existe, incrementar cantidad
            const quantityInput = existingRow.find('.quantity');
            const currentQty = parseInt(quantityInput.val()) || 0;
            quantityInput.val(currentQty + 1);
        } else {
            // Si no existe, agregar nueva fila
            const newRow = `
                <tr data-product-id="${productId}">
                    <td>${selectedProduct.category.nombre}</td>
                    <td>${selectedProduct.nombre}</td>
                    <td>
                        <select class="form-control">
                            <option value="">Seleccione un turno</option>
                            <option value="0">Mañana</option>
                            <option value="1">Tarde</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control">
                            <option value="">Seleccione una sede</option>
                            @foreach ($headquarters as $headquarter)
                            <option value="{{ $headquarter->id }}">{{ $headquarter->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="form-control">
                            <option value="">Seleccione un Encargado</option>
                            @foreach ($encargados as $encargado)
                            <option value="{{ $encargado->id }}">{{ $encargado->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" class="form-control text-end price" min="0.01" step="0.01"></td>
                    <td><input type="number" class="form-control text-end quantity" min="0.01" step="0.01"></td>
                    <td><input type="number" class="form-control text-end subtotal" min="0.01" step="0.01"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#productionTable tbody').append(newRow);
            attachEventsToRows();
        }

        // Limpiar campo de búsqueda
        $('#search-product').val('');
    }




    function attachEventsToRows() {
        $('#productionTable').on('input', '.quantity, .price', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const subtotalField = row.find('.subtotal');

            // Calcular subtotal basado en cantidad y precio unitario
            if (quantity > 0 && price > 0) {
                const subtotal = (quantity * price).toFixed(2);
                subtotalField.val(subtotal);
            } else {
                subtotalField.val('');
            }

            // Actualizar total general
            updateTotal();
        });

        // También actualizar cuando se cambie el subtotal manualmente
        $('#productionTable').on('input', '.subtotal', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const subtotal = parseFloat(row.find('.subtotal').val()) || 0;
            const priceField = row.find('.price');

            // Calcular precio unitario basado en cantidad y subtotal
            if (quantity > 0 && subtotal > 0) {
                const unitPrice = (subtotal / quantity).toFixed(2);
                priceField.val(unitPrice);
            }

            // Actualizar total general
            updateTotal();
        });
    }


    $('#productionForm').on('submit', function(e) {
        e.preventDefault();

        let productsCart = [];

        $('#productionTable tbody tr').each(function() {
            let row = $(this);
            let productId = row.data('product-id');
            let quantity = parseFloat(row.find('.quantity').val());
            let price = parseFloat(row.find('.price').val());
            let turno = row.find('select').eq(0).val(); // Primer select (turno)
            let headquarterId = row.find('select').eq(1).val(); // Segundo select (sede)
            let encargado = row.find('select').eq(2).val(); // Tercer select (encargado)

            if (productId && quantity >= 0.01 && price >= 0 && turno !== '' && headquarterId !== '') {
                const item = {
                    product_id: productId,
                    quantity: quantity,
                    price: price,
                    turno: parseInt(turno),
                    headquarter_id: parseInt(headquarterId),
                    staff_id: parseInt(encargado)
                };

                // AGREGAR DATOS ADICIONALES PARA PRODUCTOS NUEVOS (ID NEGATIVO)
                if (productId < 0) {
                    // Obtener category_id y nombre desde los data attributes
                    item.category_id = row.data('category-id');
                    item.nombre = row.data('nombre');
                    item.unidad_medida = row.data('unidad-medida');

                    // Validar que estos datos existan
                    if (!item.category_id || !item.nombre) {
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error: Faltan datos del producto nuevo en la fila'
                        });
                        return false; // Detener el procesamiento
                    }
                }

                productsCart.push(item);
            }
        });

        // Validación corregida - debe ser === 0, no === 1
        if (productsCart.length === 0) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'Debe agregar al menos un Producto con turno y sede válidos'
            });
            return;
        }

        // Mostrar spinner
        const spinner = document.getElementById('global-spinner');
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        // Preparar los datos para enviar
        let data = {
            _token: $('input[name="_token"]').val(),
            products: JSON.stringify(productsCart),
        };

        // Debug: mostrar los datos que se van a enviar
        console.log("Datos a enviar:", data);
        console.log("Products Cart:", productsCart);

        // Enviar los datos mediante AJAX
        
        $.ajax({
            url: '{{ $storeRoute }}',
            method: 'POST',
            data: data,
            success: function(response) {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');

                if (response.status) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Operación exitosa'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // Error del backend
                    ToastError.fire({
                        text: response.error || 'Ocurrió un error'
                    });
                }
            },

           error: function(xhr, status, error) {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');

            console.log("Error en la petición:");
            console.log("Products enviados:", productsCart);
            console.log("XHR Response:", xhr);
            console.log("XHR Status:", status);
            console.log("XHR Error:", error);

            let mensaje = 'Ocurrió un error al procesar la producción';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    mensaje = xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
            } else if (xhr.responseText) {
                mensaje = xhr.responseText;
            }

            ToastError.fire({
                text: mensaje
            });
        }

        });
    });

    function updateTotal() {
        let total = 0;

        $('#productionTable tbody tr').each(function() {
            let quantity = parseFloat($(this).find('.quantity').val()) || 0;
            let price = parseFloat($(this).find('.price').val()) || 0;
            total += (quantity * price);
        });

        $('#totalAmount').text(total.toFixed(2));
    }

   
    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('#productionTable tbody tr').each(function() {
                var nombre = $(this).find('td:eq(1)').text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    // Evento para eliminar fila
    $('#productionTable').on('click', '.delete-row', function () {
        $(this).closest('tr').remove();
        updateTotal(); // actualizar total
    });

</script>
@endsection