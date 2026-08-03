@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-primary active" href="{{ route('consumption.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 20px 5px 20px;">
        <a class="nav-link btn btn-secondary" href="{{ route('consumption.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h2>Salidas</h2>
<p>Registro de nueva salida</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <form id="consumptionForm">
                        @csrf

                        <div class="row mb-12">
                            <!-- Fila con Producto, Fecha y Agregar Encargado -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <label class="form-label">Producto</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="busquedaProducto"
                                                placeholder="Buscar producto...">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label">Fecha</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="date" value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarStaff">
                                            Agregar Encargado
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Lado derecho: Registrar salidas -->
                            <div class="col-md-3">
                                <div class="d-flex justify-content-end align-items-end h-100">
                                    <button type="submit" class="btn btn-primary" id="saveConsumption">Registrar salidas</button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="table-responsive" id="comsumptionTable">
                            <table class="table table-striped" id="consumptionTable">
                                <thead>
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Unidad</th>
                                        <th>Stock</th>
                                        <th>Panadería</th>
                                        <!-- <th>Merma</th> -->
                                        <th>Pastelería</th>
                                        <th>Cocina</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>
                                            <select class="form-control" id="encargado-panaderia" class="encargados">
                                                <option value="">Seleccione un encargado</option>
                                                @foreach ($empleadosPanaderia as $empleado)
                                                <option value="{{ $empleado->id }}">{{ $empleado->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" id="encargado-pasteleria" class="encargados">
                                                <option value="">Seleccione un encargado</option>
                                                @foreach ($empleadosPasteleria as $empleado)
                                                <option value="{{ $empleado->id }}">{{ $empleado->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" id="encargado-cocina" class="encargados">
                                                <option value="">Seleccione un encargado</option>
                                                @foreach ($empleadosCocina as $empleado)
                                                <option value="{{ $empleado->id }}">{{ $empleado->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    @foreach ($productos as $producto)
                                    <tr>
                                        <td>{{ $producto->product->nombre }}</td>
                                        <td>{{ $producto->product->unidad_medida }}</td>
                                        <td>{{ $producto->quantity }}</td>
                                        <td>
                                            <input type="number" class="form-control cantidad-input" min="0" step="0.01"
                                                data-product-id="{{ $producto->product_id }}"
                                                data-stock="{{ $producto->quantity }}"
                                                data-area="panaderia"
                                                data-encargado=""
                                                data-nombre="{{ $producto->product->nombre }}" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control cantidad-input" min="0" step="0.01"
                                                data-product-id="{{ $producto->product_id }}"
                                                data-stock="{{ $producto->quantity }}"
                                                data-area="pasteleria"
                                                data-encargado=""
                                                data-nombre="{{ $producto->product->nombre }}" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control cantidad-input" min="0" step="0.01"
                                                data-product-id="{{ $producto->product_id }}"
                                                data-stock="{{ $producto->quantity }}"
                                                data-area="cocina"
                                                data-encargado=""
                                                data-nombre="{{ $producto->product->nombre }}" placeholder="0.00">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary" id="saveConsumption">Registrar
                                salidas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregarStaff" tabindex="-1" aria-labelledby="modalAgregarStaffLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarStaffLabel">Agregar Encargado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAgregarStaff">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreStaff" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombreStaff" name="nombre" required
                            placeholder="Ingrese el nombre completo">
                    </div>
                    <div class="mb-3">
                        <label for="puestoStaff" class="form-label">Puesto *</label>
                        <select class="form-control" id="puestoStaff" name="puesto_id" required>
                            <option value="">Seleccione un puesto</option>
                            @foreach($puestos as $puesto)
                                <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Agregar</button>
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
<script>
    function actualizarSelectsEmpleados(nuevoEmpleado) {
        // Mapeo de puesto_id a select correspondiente
        const puestoToSelect = {
            6: '#encargado-panaderia',    // Panadería
            7: '#encargado-pasteleria',   // Pastelería  
            8: '#encargado-cocina'        // Cocina
        };
        
        // Obtener el select correspondiente al puesto del nuevo empleado
        const selectId = puestoToSelect[nuevoEmpleado.puesto_id];
        
        if (selectId) {
            const select = document.querySelector(selectId);
            if (select) {
                const option = document.createElement('option');
                option.value = nuevoEmpleado.id;
                option.textContent = nuevoEmpleado.nombre;
                select.appendChild(option);
            }
        }
    }

    document.getElementById('formAgregarStaff').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('_token', $('input[name="_token"]').val());
        fetch('{{ route("staff.storeAjax") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarStaff'));
                    modal.hide();

                    // Limpiar formulario
                    this.reset();

                    // Actualizar los selects con el nuevo empleado
                    actualizarSelectsEmpleados(data.data);

                    // Mostrar mensaje de éxito
                    if (typeof ToastMessage !== 'undefined') {
                        ToastMessage.fire({
                            icon: 'success',
                            text: 'Encargado agregado exitosamente'
                        });
                    } else {
                        alert('Encargado agregado exitosamente');
                    }
                } else {
                    if (typeof ToastMessage !== 'undefined') {
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error: ' + data.message
                        });
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof ToastMessage !== 'undefined') {
                    ToastMessage.fire({
                        icon: 'error',
                        text: 'Error al agregar el encargado'
                    });
                } else {
                    alert('Error al agregar el encargado');
                }
            });
    });

    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            //no buscar en la primera fila donde se escoge el encargado
            $('#comsumptionTable tbody tr').slice(1).each(function() {
                var nombre = $(this).find('td:first').text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    function seleccionarArea(area, event) {
        const parent = event.target.closest('.btn-group');
        Array.from(parent.children).forEach(child => {
            child.classList.remove('active');
        });
        event.target.classList.add('active');
        document.getElementById('area').value = area;
    }

    $('#consumptionForm').on('submit', function(e) {
        e.preventDefault();

        let consumptions = [];
        let stop = false;

        // Recorremos cada fila con cantidad ingresada
        $('.cantidad-input').each(function() {
            if (stop) return false;

            let cantidad = parseFloat($(this).val());

            // Solo tomar si cantidad válida
            if (!isNaN(cantidad) && cantidad > 0) {
                let id = $(this).data('product-id');
                let stock = parseFloat($(this).data('stock'));
                let nombre = $(this).data('nombre');
                let area = $(this).data('area');
                let encargado_id = $(this).attr('data-encargado');
                console.log(encargado_id)
                if (cantidad > stock) {
                    ToastMessage.fire({
                        icon: 'error',
                        text: `La cantidad ingresada de "${nombre}" supera el stock disponible (${stock})`
                    });
                    //alert(`La cantidad ingresada de "${nombre}" supera el stock disponible (${stock})`);
                    stop = true;
                    return false;
                }

                if (!encargado_id || encargado_id === "") {
                    ToastMessage.fire({
                        icon: 'error',
                        text: 'Debe elegir el encargado.'
                    });
                    stop = true;
                    return false;
                }

                consumptions.push({
                    product_id: id,
                    quantity: cantidad,
                    area: area,
                    encargado: encargado_id
                    //merma: merma,
                    //notes: notes
                });
            }
        });

        if (stop) {
            return;
        }

        if (consumptions.length === 0) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe ingresar al menos un consumo válido.'
            });
            return;
        }

        // Obtener la fecha del input
        let fecha = $('#date').val();
        if (!fecha) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe seleccionar una fecha.'
            });
            return;
        }

        // Enviar usando FormData para simular form tradicional
        let formData = new FormData();
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('date', fecha);
        formData.append('consumptions', JSON.stringify(consumptions));

        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        $.ajax({
                url: '{{ route('consumption.store') }}',
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function(response) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: 'Consumos registrados correctamente.'
                    });
                    window.location.href = '{{ route('consumption.index') }}';
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error al registrar comsumos.'
                        });
                    } else {
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error al registrar comsumos.'
                        });
                    }

                }
            })
            .always(function() {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');
            });
    });

    // Permitir solo 2 decimales en campo cantidad
    $(document).on('input', '.cantidad-input', function() {
        let val = $(this).val();
        if (!/^\d*(\.\d{0,2})?$/.test(val)) {
            $(this).val(val.slice(0, -1));
        }
    });

    $('#encargado-panaderia').on('change', function() {
        let val = $(this).val();
        // Actualiza todos los inputs de panadería
        $('input[data-area="panaderia"]').attr('data-encargado', val);
    });

    $('#encargado-pasteleria').on('change', function() {
        let val = $(this).val();
        // Actualiza todos los inputs de pastelería
        $('input[data-area="pasteleria"]').attr('data-encargado', val);
    });

    $('#encargado-cocina').on('change', function() {
        let val = $(this).val();
        // Actualiza todos los inputs de pastelería
        $('input[data-area="cocina"]').attr('data-encargado', val);
    });
</script>
@endsection