@extends('template.index')

@section('nav')

@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif

@endsection

@section('header')
<h2>Egresos de Caja</h2>
<p>Registrar egresos de caja</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">

                        <!-- Formulario principal de datos de la compra -->
                        <form id="expensecash">
                            @csrf

                            <!-- fila1 -->
                            <div class="row mb-3">
                                <div class="col-sm-6 d-flex align-items-end position-relative">
                                    <label class="col-form-label me-2" for="motivo">Motivo:</label>
                                    <input type="text" class="form-control me-2" id="producto" name="producto" placeholder="Motivo del egreso">
                                    <button type="button" class="btn btn-primary" id="addRowBtn">
                                        Agregar
                                    </button>
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

                                        <button type="submit" class="btn btn-success ms-2" id="saveExpenseCash">
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de productos agregados -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="productionTable">
                                    <thead class="table">
                                        <tr>
                                            <th>Motivo</th>
                                            <th>N° Comprobante</th>
                                            <th>Monto</th>
                                            <th>Decripción</th>
                                            <th class="d-none">Cantidad</th>
                                            <th class="d-none">Subtotal</th>
                                            <th>Acción</th>
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
@endsection

@section('scripts')
<script>
    let selectedProductId = null;
    $(document).ready(function() {
        const $input = $('#producto');

        $input.on('input', function() {
            const query = $input.val().trim();
            $('.autocomplete-item').remove();
            selectedProductId = null;

            if (query.length < 2) return;

            $.get("{{ route('buscar.miscelaneo') }}", {
                q: query
            }, function(data) {
                if (!data.length) return;

                // Crear lista contenedora
                const $list = $('<div></div>')
                    .addClass('autocomplete-list')
                    .css({
                        position: 'absolute',
                        background: '#fff',
                        border: '1px solid #ccc',
                        width: $input.outerWidth(),
                        zIndex: 1000,
                        top: $input.position().top + $input.outerHeight(),
                        left: $input.position().left,
                        maxHeight: '220px',
                        overflowY: 'auto',
                        borderRadius: '4px',
                        boxShadow: '0 2px 8px rgba(0,0,0,0.08)'
                    });

                data.slice(0, 10).forEach(function(prod) {
                    const $item = $('<div></div>')
                        .addClass('autocomplete-item p-2')
                        .css({
                            cursor: 'pointer'
                        })
                        .text(prod.nombre)
                        .on('mousedown', function() {
                            $input.val(prod.nombre);
                            selectedProductId = prod.id;
                            $('.autocomplete-list').remove();
                        })
                        .on('mouseenter', function() {
                            $('.autocomplete-item').removeClass('bg-primary text-white');
                            $(this).addClass('bg-primary text-white');
                        })
                        .on('mouseleave', function() {
                            $(this).removeClass('bg-primary text-white');
                        });
                    $list.append($item);
                });

                $input.after($list);
            });
        });

        $(document).on('mousedown', function(e) {
            if (!$(e.target).closest('#producto, .autocomplete-list').length) {
                $('.autocomplete-list').remove();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const addRowBtn = document.getElementById('addRowBtn');
        const tableBody = document.querySelector('#productionTable tbody');

            // Función para calcular y mostrar el total
            function updateTotalAmount() {
                let total = 0;
                tableBody.querySelectorAll('.subtotal').forEach(function(input) {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('totalAmount').textContent = total.toFixed(2);
            }

        addRowBtn.addEventListener('click', function() {
            // Obtener solo el motivo y el id
            const producto = document.getElementById('producto').value;

            // Validar campo obligatorio
            if (!producto || !selectedProductId) {
                alert('Debes seleccionar un producto válido del listado antes de agregar.');
                return;
            }

            // Crear nueva fila: solo motivo fijo, los demás como inputs vacíos
            const motivoLower = producto.trim().toLowerCase();
            const ventaEnabled = motivoLower === 'delivery' ? '' : 'disabled';
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${producto}<input type="hidden" class="producto-id" value="${selectedProductId}"></td>
                <td><input type="text" class="form-control form-control-sm" value="" ${ventaEnabled}></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm precio-unitario" value=""></td>
                <td><input type="text" class="form-control form-control-sm" value=""></td>
                <td class="d-none"><input type="number" min="1" class="form-control form-control-sm cantidad" value="1"></td>
                <td class="d-none"><input type="number" step="0.01" class="form-control form-control-sm subtotal" value="" readonly></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash-fill"></i></button>
                </td>
            `;

            tableBody.appendChild(newRow);

            // Limpiar solo el campo motivo y el id
            document.getElementById('producto').value = '';
            selectedProductId = null;

            // Calcular subtotal al editar precio o cantidad
            const precioInput = newRow.querySelector('.precio-unitario');
            const cantidadInput = newRow.querySelector('.cantidad');
            const subtotalInput = newRow.querySelector('.subtotal');
            function updateSubtotal() {
                const precio = parseFloat(precioInput.value) || 0;
                const cantidad = parseFloat(cantidadInput.value) || 1;
                subtotalInput.value = (precio * cantidad).toFixed(2);
                    updateTotalAmount();
            }
            precioInput.addEventListener('input', updateSubtotal);
            cantidadInput.addEventListener('input', updateSubtotal);
                // Actualizar total al agregar fila
                updateTotalAmount();
        });

        // Eliminar fila
        tableBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                    updateTotalAmount();
            }
        });

        // Enviar datos al backend
        document.getElementById('expensecash').addEventListener('submit', function(e) {
            e.preventDefault();
            const rows = tableBody.querySelectorAll('tr');
            const expensecash = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                expensecash.push({
                    motivo: cells[0].childNodes[0].nodeValue.trim(),
                    producto_id: cells[0].querySelector('.producto-id')?.value || null,
                    venta: cells[1].querySelector('input')?.value || null,
                    precio_unitario: cells[2].querySelector('input')?.value || null,
                    descripcion: cells[3].querySelector('input')?.value || null,
                    cantidad: cells[4].querySelector('input')?.value || 1,
                    subtotal: cells[5].querySelector('input')?.value || 0
                });
            });

            $.ajax({
                url: "{{ route('store.expensecash') }}",
                method: 'POST',
                data: {
                    expensecash: expensecash,
                    _token: $('input[name="_token"]').val()
                },
                success: function(resp) {
                    ToastMessage.fire({
                        icon: 'success',
                        title: resp.message || 'Egresos registrados correctamente.'
                    });
                    tableBody.innerHTML = '';
                        updateTotalAmount();
                },
                error: function(xhr) {
                    ToastError.fire({
                        icon: 'error',
                        text: xhr.responseJSON?.message || 'Error al registrar egresos'
                    });
                }
            });
        });
    });
</script>
@endsection