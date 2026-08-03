@extends('template.index')

@section('header')
<h1>Gestión de Contratos</h1>
<p>Administración de contratos, órdenes y áreas asociadas.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <!-- Card que contiene el formulario y la tabla -->
    <div class="card shadow">
        <!-- Cuerpo del Card -->
        <div class="card-body">
            <!-- Formulario de Registro de Contrato -->
            <form id="formContrato" class="mb-5">
                <!-- Fila 1: Cliente y RUC -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Cliente</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="cliente" placeholder="Ingrese el nombre del cliente" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">RUC</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="ruc" placeholder="Ingrese el RUC" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 2: Productos del Contrato -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-12">
                        <!-- Producto 1: Gasolina 1 -->
                        <div class="row mb-3 align-items-center producto">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 1</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="galones_gasolina_1" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="precio_gasolina_1" placeholder="Precio unitario" required>
                            </div>
                        </div>
                        <!-- Producto 2: Gasolina 2 -->
                        <div class="row mb-3 align-items-center producto">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 2</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="galones_gasolina_2" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="precio_gasolina_2" placeholder="Precio unitario" required>
                            </div>
                        </div>
                        <!-- Producto 3: Gasolina 3 -->
                        <div class="row mb-3 align-items-center producto">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 3</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="galones_gasolina_3" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="precio_gasolina_3" placeholder="Precio unitario" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardar Contrato -->
                <div class="row mb-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Contratos -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>RUC</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ejemplo de datos estáticos -->
                        <tr>
                            <td>1</td>
                            <td>Cliente 1</td>
                            <td>12345678901</td>
                            <td>
                                <ul>
                                    <li>Gasolina 1 - 100 galones - S/ 10.00</li>
                                    <li>Gasolina 2 - 50 galones - S/ 12.00</li>
                                    <li>Gasolina 3 - 30 galones - S/ 15.00</li>
                                </ul>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning">Editar</button>
                                <button type="button" class="btn btn-sm btn-danger">Eliminar</button>
                                <button type="button" class="btn btn-sm btn-info" onclick="verOrdenes(1)">Ver Órdenes</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Órdenes -->
    <!-- Modal para Ver Órdenes -->
    <!-- Modal para Ver Órdenes -->
    <div class="modal fade" id="modalOrdenes" tabindex="-1" aria-labelledby="modalOrdenesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOrdenesLabel">Órdenes del Contrato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de Registro de Órdenes -->
                    <form id="formOrden" class="mb-4">
    
                        <!-- Producto 1: Gasolina 1 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 1 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_1" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Producto 2: Gasolina 2 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 2 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_2" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Producto 3: Gasolina 3 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 3 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_3" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Botón de Agregar Orden -->
                        <div class="row mb-3">
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">Agregar Orden</button>
                            </div>
                        </div>
                    </form>
    
                    <!-- Tabla de Órdenes -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Gasolina 1</th>
                                    <th>Gasolina 2</th>
                                    <th>Gasolina 3</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaOrdenes">
                                <!-- Datos dinámicos de órdenes -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Áreas -->
    <div class="modal fade" id="modalAreas" tabindex="-1" aria-labelledby="modalAreasLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAreasLabel">Áreas de la Orden</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de Registro de Área -->
                    <form id="formArea" class="mb-4">
                        <!-- Campo Área -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Área</label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-select" name="area">
                                    <option value="">Seleccione un área</option>
                                    <option value="ventas">Ventas</option>
                                    <option value="logistica">Logística</option>
                                    <option value="almacen">Almacén</option>
                                </select>
                            </div>
                        </div>
    
                        <!-- Producto 1: Gasolina 1 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 1 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_1" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Producto 2: Gasolina 2 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 2 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_2" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Producto 3: Gasolina 3 -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 3 - Cantidad</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="cantidad_gasolina_3" placeholder="Cantidad">
                            </div>
                        </div>
    
                        <!-- Botón de Agregar Área -->
                        <div class="row mb-3">
                            <div class="col-md-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">Agregar Área</button>
                            </div>
                        </div>
                    </form>
    
                    <!-- Tabla de Áreas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Área</th>
                                    <th>Gasolina 1</th>
                                    <th>Gasolina 2</th>
                                    <th>Gasolina 3</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAreas">
                                <!-- Datos dinámicos de áreas -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lógica para ver órdenes de un contrato
    function verOrdenes(contratoId) {
        // Simulación de datos de órdenes
        const ordenes = [
            { id: 1, gasolina1: "100 galones", gasolina2: "50 galones", gasolina3: "30 galones" },
            { id: 2, gasolina1: "200 galones", gasolina2: "100 galones", gasolina3: "60 galones" },
        ];

        const tablaOrdenes = document.getElementById('tablaOrdenes');
        tablaOrdenes.innerHTML = ordenes.map(orden => `
            <tr>
                <td>${orden.id}</td>
                <td>${orden.gasolina1}</td>
                <td>${orden.gasolina2}</td>
                <td>${orden.gasolina3}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-info" onclick="verAreas(${orden.id})">Ver Áreas</button>
                    <button type="button" class="btn btn-sm btn-danger">Eliminar</button>
                </td>
            </tr>
        `).join('');

        // Mostrar el modal de órdenes
        const modal = new bootstrap.Modal(document.getElementById('modalOrdenes'));
        modal.show();
    }

    // Lógica para ver áreas de una orden
    function verAreas(ordenId) {
        // Simulación de datos de áreas
        const areas = [
            { id: 1, area: "Ventas", gasolina1: "50 galones", gasolina2: "30 galones", gasolina3: "20 galones" },
            { id: 2, area: "Logística", gasolina1: "100 galones", gasolina2: "60 galones", gasolina3: "40 galones" },
        ];

        const tablaAreas = document.getElementById('tablaAreas');
        tablaAreas.innerHTML = areas.map(area => `
            <tr>
                <td>${area.id}</td>
                <td>${area.area}</td>
                <td>${area.gasolina1 || 'N/A'}</td>
                <td>${area.gasolina2 || 'N/A'}</td>
                <td>${area.gasolina3 || 'N/A'}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning">Editar</button>
                    <button type="button" class="btn btn-sm btn-danger">Eliminar</button>
                </td>
            </tr>
        `).join('');

        // Mostrar el modal de áreas
        const modal = new bootstrap.Modal(document.getElementById('modalAreas'));
        modal.show();
    }
</script>
@endsection