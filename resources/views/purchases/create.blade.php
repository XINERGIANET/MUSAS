@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('purchases.create', ['tipo' => $tipo]) }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('purchases.index', ['tipo' => $tipo]) }}">Histórico</a>
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
    <p>{{ $subtitle }}</p>
@endsection


@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">

                        <!-- Formulario principal de datos de la compra -->
                        <form id="purchaseForm">
                            <input type="hidden" id="tipo" value="{{ $tipo ?? 'compra' }}">

                            @csrf

                            <p><strong>Movimientos</strong></p>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label text-start">Proveedor:</label>
                                <div class="col-sm-5">
                                    <input type="text" id="search-supplier" class="form-control" placeholder="Buscar proveedor...">
                                    <input type="hidden" id="supplier_id" name="supplier_id">
                                </div>
                                <div class="col-sm-2 mb-3">
                                    <a class="btn btn-primary" id="addProvider" data-bs-toggle="modal" data-bs-target="#providerModal">
                                        <i class="fas fa-user-plus"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-3 col-form-label text-start">Buscar Producto:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="search-product" name="search-product" placeholder="Buscar producto...">
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <a class="btn btn-success" id="addProduct">
                                        <i class="fas fa-plus"></i> Nuevo Producto
                                    </a>
                                </div>
                            </div>

                            <div id="nuevoproducto" style="display: none;">
                                <div class="row align-items-end mb-4">
                                    <div class="col-md-3">
                                        <label for="category_id" class="form-label">Categoría <!-- <span class="text-danger">*</span> --></label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del producto">
                                    </div>

                                    <div class="col-md-3">
                                        <select class="form-control" id="unidad_medida" name="unidad_medida">
                                            <option value="">Seleccione</option>
                                            @foreach ($unidadMedidas as $unidad)
                                            <option value="{{ $unidad->nombre }}">{{ $unidad->nombre }}</option>
                                            @endforeach
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

                            <p><strong>Detalle Compra</strong></p>

                            <div class="mb-4 row">
                                <label class="col-sm-3 col-form-label text-start">Tipo de Comprobante</label>
                                <div class="col-sm-3">
                                    <select class="form-select" id="tipo_comprobante" name="tipo_comprobante" required>
                                        <option value="">Seleccione</option>
                                        <option value="1">Factura</option>
                                        <option value="2">Boleta</option>
                                        <option value="3">Nota de Venta</option>
                                        <option value="4">Otro</option>
                                    </select>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">N° Comprobante (*)</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="invoiceNumber" name="invoice_number" >
                                </div>
                            </div>

                            <div class="mb-4 row">
                                <label class="col-sm-3 col-form-label text-start">Método de Pago</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="paymentMethod" name="payment_method_id" required>
                                        <option value="">Seleccione un método</option>
                                        @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">Fecha de Compra</label>
                                <div class="col-sm-3">
                                    <input type="date" class="form-control border-dark" id="purchaseDate" name="date" required>
                                </div>
                            </div>

                            <input type="hidden" name="tipo" id="tipo" value="{{ $tipo }}">

                            <hr style="border: none; border-top: 2px solid #888; margin: 20px 0;">

                            <div class="col-sm-12 mb-3">
                                <p><strong>Filtro Búsqueda</strong></p>
                                
                                <div class="row align-items-end">
                                    <!-- Búsqueda al inicio -->
                                    <div class="col-md-4">
                                        <label class="form-label">Producto</label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control"
                                                id="busquedaProducto"
                                                placeholder="Buscar producto...">
                                        </div>
                                    </div>
                                    
                                    <!-- Espacio en el medio -->
                                    <div class="col-md-4">
                                    </div>
                                    
                                    <!-- Total y botón al final -->
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            <strong>Total: S/ <span id="totalAmount">0.00</span></strong>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="savePurchase">
                                            Guardar Compra
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Tabla de productos agregados -->

                            

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="purchaseTable">
                                    <thead class="table">
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Producto</th>
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


<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerModalLabel">Agregar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="providerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ruc" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="razon_social" class="form-label">Razón Social</label>
                                <input type="text" class="form-control" id="razon_social">
                            </div>
                        </div>
                        <input hidden class="form-control" id="tipo" value="C">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveProovider">
                    <i class="fas fa-save"></i> Guardar
                </button>
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
    document.getElementById('saveProduct').addEventListener('click', function() {
        var categoryId = document.getElementById('category_id').value;
        var nombre = document.getElementById('nombre').value.trim();
        var unidadMedida = document.getElementById('unidad_medida').value;

        if (unidadMedida === "") {
            ToastMessage.fire({
                icon: 'warning',
                text: 'Debe seleccionar una unidad de medida'
            });
            return;
        }

        // Validaciones básicas
        if (nombre === "") {
            ToastMessage.fire({
                icon: 'warning',
                text: 'El nombre del producto es obligatorio'
            });
            return;
        }

        if (categoryId === "") {
            ToastMessage.fire({
                icon: 'warning',
                text: 'Debe seleccionar una categoría'
            });
            return;
        }

        // Validaciones adicionales del nombre
        if (nombre.length < 3) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'El nombre debe tener al menos 3 caracteres'
            });
            return;
        }

        if (nombre.length > 100) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'El nombre no puede exceder 100 caracteres'
            });
            return;
        }

        // Validar caracteres repetidos o formato extraño
        var nombreLimpio = nombre.replace(/\s+/g, ' '); // Reemplazar múltiples espacios por uno solo
        var palabras = nombreLimpio.split(' ');
        
        // Verificar palabras duplicadas consecutivas
        for (let i = 0; i < palabras.length - 1; i++) {
            if (palabras[i].toLowerCase() === palabras[i + 1].toLowerCase()) {
                ToastMessage.fire({
                    icon: 'warning',
                    text: 'El nombre contiene palabras duplicadas'
                });
                return;
            }
        }

        // Verificar caracteres especiales extraños (permitir solo letras, números, espacios y algunos símbolos básicos)
        var caracteresPermitidos = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-_|().]+$/;
        if (!caracteresPermitidos.test(nombre)) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'El nombre contiene caracteres no válidos'
            });
            return;
        }

        // Verificar que no tenga solo números o caracteres especiales
        var tieneLetras = /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(nombre);
        if (!tieneLetras) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'El nombre debe contener al menos una letra'
            });
            return;
        }

        // Usar el nombre limpio para continuar
        nombre = nombreLimpio;

        // Verificar si el producto ya existe en la base de datos
        var saveBtn = this;
        var originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        saveBtn.disabled = true;

        // AJAX para verificar existencia del producto
        $.ajax({
            url: '{{ route("products.check") }}', // Necesitarás crear esta ruta
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                nombre: nombre,
                category_id: categoryId
            },
            success: function(response) {
                if (response.exists) {
                    // El producto ya existe
                    ToastMessage.fire({
                        icon: 'warning',
                        text: `El producto "${nombre}" ya existe en esta categoría`
                    });
                    
                    // Restaurar botón
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                    return;
                }
                
                // Si no existe, proceder a agregarlo
                proceedToAddProduct(categoryId, nombre, unidadMedida, saveBtn, originalText);
            },
            error: function(xhr) {
                console.error('Error al verificar producto:', xhr);
                ToastMessage.fire({
                    icon: 'error',
                    text: 'Error al verificar el producto'
                });
                
                // Restaurar botón
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        });
    });

    // Función para proceder con la adición del producto (CORREGIDA)
    function proceedToAddProduct(categoryId, nombre, unidadMedida, saveBtn, originalText) {
        // Obtener el nombre de la categoría seleccionada
        var categorySelect = document.getElementById('category_id');
        var categoryName = categorySelect.options[categorySelect.selectedIndex].text;

        // Generar un ID temporal único (negativo para diferenciarlo de los reales)
        var tempId = -Date.now();

        // Verificar si ya existe un producto con el mismo nombre en la tabla actual
        var existingRow = null;
        $('#purchaseTable tbody tr').each(function() {
            var existingName = $(this).find('td:eq(1)').text().trim();
            if (existingName.toLowerCase() === nombre.toLowerCase()) {
                existingRow = $(this);
                return false; // break del each
            }
        });

        if (existingRow) {
            // Si existe en la tabla, incrementar cantidad
            const quantityInput = existingRow.find('.quantity');
            const currentQty = parseInt(quantityInput.val()) || 0;
            quantityInput.val(currentQty + 1);
            
            ToastMessage.fire({
                icon: 'info',
                text: 'Producto ya existe en la tabla, cantidad incrementada'
            });
        } else {
            // Si no existe, agregar nueva fila CON los datos necesarios
            const newRow = `
                <tr data-product-id="${tempId}" 
                    data-type="Producto" 
                    data-category-id="${categoryId}" 
                    data-nombre="${nombre}"
                    data-unidad-medida="${unidadMedida}">
                    <td>${categoryName}</td>
                    <td>${nombre}</td>
                    <td><input type="number" class="form-control text-end price" disabled></td>
                    <td><input type="number" class="form-control text-end quantity" min="0.01" step="0.01" value="1"></td>
                    <td><input type="number" class="form-control text-end subtotal" min="0.01" step="0.01"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#purchaseTable tbody').append(newRow);
            attachEventsToRows();

            ToastMessage.fire({
                icon: 'success',
                text: 'Producto agregado a la tabla'
            });
        }

        // Limpiar el formulario
        document.getElementById('category_id').value = '';
        document.getElementById('nombre').value = '';
        document.getElementById('unidad_medida').value = '';

        // Ocultar el formulario
        const form = document.getElementById('nuevoproducto');
        form.style.display = 'none';
        document.getElementById('addProduct').innerHTML = '<i class="fas fa-plus"></i> Nuevo Producto';
        
        // Restaurar botón
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }

    function collectTableData() {
        const products = [];
        
        $('#purchaseTable tbody tr').each(function() {
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

    document.getElementById('addProduct').addEventListener('click', function(e) {
        e.preventDefault(); // Prevenir comportamiento del enlace
        const form = document.getElementById('nuevoproducto');
        const isVisible = form.style.display !== 'none';
        if (isVisible) {
            form.style.display = 'none';
            this.innerHTML = '<i class="fas fa-plus"></i> Nuevo Producto';
        } else {
            form.style.display = 'block';
            this.innerHTML = '<i class="fas fa-minus"></i> Ocultar';
        }
    });

    var suppliers = @json($suppliers);
    var newproducts = @json($products);
    var selectedProducts = [];

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
        const existingRow = $(`#purchaseTable tr[data-product-id="${productId}"]`);

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
                    <td><input type="number" class="form-control text-end price" disabled></td>
                    <td><input type="number" class="form-control text-end quantity" min="0.01" step="0.01"></td>
                    <td><input type="number" class="form-control text-end subtotal" min="0.01" step="0.01"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#purchaseTable tbody').append(newRow);
            attachEventsToRows();
        }

        // Limpiar campo de búsqueda
        $('#search-product').val('');
    }

    $('#search-supplier').autocomplete({
            source: function(request, response) {
                var matches = $.grep(suppliers, function(item) {
                    return item.razon_social.toLowerCase()
                        .includes(request.term.toLowerCase());
                });
                matches = matches.slice(0, 10);
                var results = $.map(matches, function(item) {
                    return {
                        label: item.razon_social,
                        value: item.razon_social,
                        id: item.id
                    };
                });
                response(results);
            },
            select: function(event, ui) {
                $('#supplier_id').val(ui.item.id); // Guardar el ID en campo oculto
                cargarProductosProveedor(ui.item.id);
            },
            appendTo: '.container-fluid'
        })
        .autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
                .appendTo(ul);
        };

    function cargarProductosProveedor(supplierId) {
        $.ajax({
            url: `{{ url('/proveedor') }}/${supplierId}/productos`,
            method: 'GET',
            success: function(productos) {
                let tbody = $('#purchaseTable tbody');
                tbody.empty();

                productos.forEach((producto, index) => {
                    tbody.append(`
                        <tr data-product-id="${producto.id}" data-type="Producto">
                            <td>${producto.category.nombre}</td>
                            <td>${producto.nombre}</td>
                            <td><input type="number" class="form-control text-end price" disabled></td>
                            <td><input type="number" class="form-control text-end quantity" min="0.01" step="0.01"></td>
                            <td><input type="number" class="form-control text-end subtotal" min="0.01" step="0.01"></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delete-row">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                attachEventsToRows(); // Vuelve a asociar eventos
            }
        });
    }

    function attachEventsToRows() {
        $('#purchaseTable').on('input', '.quantity, .subtotal', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const subtotal = parseFloat(row.find('.subtotal').val()) || 0;
            const priceField = row.find('.price');

            // Calcular precio unitario basado en cantidad y subtotal
            if (quantity > 0 && subtotal > 0) {
                const unitPrice = (subtotal / quantity).toFixed(2);
                priceField.val(unitPrice);
            } else {
                priceField.val('');
            }

            // Actualizar total general
            updateTotal();
        });
    }


    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        let productsCart = [];
        let suppliesCart = [];

        $('#purchaseTable tbody tr').each(function() {
            let row = $(this);
            let productId = row.data('product-id');
            let quantity = parseFloat(row.find('.quantity').val());
            let subtotal = parseFloat(row.find('.subtotal').val());
            let price = parseFloat(row.find('.price').val());
            let type = row.data('type');

            if (productId && quantity >= 0.01 && subtotal >= 0 && price >= 0) {
                const item = {
                    product_id: productId,
                    quantity: quantity,
                    price: price,
                    subtotal: subtotal,
                    type: type
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

                // Separar por tipo
                if (type === 'Insumo') {
                    suppliesCart.push(item);
                } else {
                    productsCart.push(item);
                }
            }
        });

        // Validación corregida - debe ser === 0, no === 1
        if (productsCart.length === 0 && suppliesCart.length === 0) {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');

            ToastMessage.fire({
                icon: 'warning',
                text: 'Debe agregar al menos un Producto o Insumo'
            });

            return;
        }

        // Mostrar spinner
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        // Preparar los datos para enviar
        let data = {
            _token: $('input[name="_token"]').val(),
            supplier_id: $('#supplier_id').val(),
            tipo_comprobante: $('#tipo_comprobante').val(),
            invoice_number: $('#invoiceNumber').val(),
            payment_method_id: $('#paymentMethod').val(),
            date: $('#purchaseDate').val(),
            tipo: $('#tipo').val(),
            products: JSON.stringify(productsCart),
            insumos: JSON.stringify(suppliesCart)
        };

        // Debug: mostrar los datos que se van a enviar
        console.log("Datos a enviar:", data);
        console.log("Products Cart:", productsCart);
        console.log("Supplies Cart:", suppliesCart);

        // Enviar los datos mediante AJAX
        $.ajax({
            url: '{{ route('purchases.store') }}',
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
            console.log("Supplies enviados:", suppliesCart);
            console.log("XHR Response:", xhr);
            console.log("XHR Status:", status);
            console.log("XHR Error:", error);

            let mensaje = 'Ocurrió un error al procesar la compra';

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

        $('#purchaseTable tbody tr').each(function() {
            let subtotal = parseFloat($(this).find('.subtotal').val()) || 0;
            total += subtotal;
        });

        $('#totalAmount').text(total.toFixed(2));
    }

    document.getElementById('saveProovider').addEventListener('click', function() {
        var ruc = document.getElementById('ruc').value;
        var razonSocial = document.getElementById('razon_social').value;
        var tipo = document.getElementById('tipo').value;

        if (ruc === "") {
            alert("El RUC es obligatorio.");
            return;
        }

        var data = {
            ruc: ruc,
            razon_social: razonSocial,
            tipo: tipo
        };

        var saveBtn = this;
        var originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        saveBtn.disabled = true;

        fetch('{{ route('savep') }}', {
                    method: 'POST', // o el método que necesites
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // si usas Laravel
                    },
                    body: JSON.stringify(data),
                })
            .then(response => {
                console.log('Status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta:', data);

                if (data.success) {
                    // Mostrar mensaje de éxito
                    ToastMessage.fire({
                        icon: 'success',
                        text: data.message || 'Operación exitosa' // Corregido: usar data.message en lugar de response.message
                    }).then(() => {
                        location.reload();
                    });

                    // Cerrar modal
                    const modal = document.getElementById('providerModal');
                    if (modal) {
                        // Intentar con Bootstrap 5
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            // Fallback para jQuery/Bootstrap 4
                            if (typeof $ !== 'undefined') {
                                $('#providerModal').modal('hide');
                            }
                        }
                    }

                    // Recargar página después de un delay
                    setTimeout(() => window.location.reload(), 1000);

                } else {
                    // Mostrar error
                    alert(data.message || 'Error al agregar el proveedor');
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Restaurar estado del botón
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
    });
    
    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('#purchaseTable tbody tr').each(function() {
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
    $('#purchaseTable').on('click', '.delete-row', function () {
        $(this).closest('tr').remove();
        updateTotal(); // actualizar total
    });

</script>
@endsection