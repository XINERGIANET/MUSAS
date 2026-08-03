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
                                <input type="text" class="form-control" placeholder="Ingrese el nombre del cliente" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">RUC</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el RUC" required>
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
                                <input type="number" class="form-control" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Precio unitario" required>
                            </div>
                        </div>
                        <!-- Producto 2: Gasolina 2 -->
                        <div class="row mb-3 align-items-center producto">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 2</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Precio unitario" required>
                            </div>
                        </div>
                        <!-- Producto 3: Gasolina 3 -->
                        <div class="row mb-3 align-items-center producto">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Gasolina 3</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" placeholder="Galones" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Precio unitario" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila 3: Órdenes -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-12">
                        <h6>Órdenes</h6>
                        <div id="ordenesContrato">
                            <!-- Orden 1 -->
                            <div class="row mb-3 align-items-center orden">
                                <div class="col-md-4">
                                    <label class="form-label mb-0">Orden 1</label>
                                </div>
                                <div class="col-md-8">
                                    <!-- Áreas de la Orden 1 -->
                                    <div id="areasOrden1">
                                        <div class="row mb-3 align-items-center area">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" placeholder="Área (opcional)">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" class="form-control" placeholder="Cantidad (opcional)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarArea(this)">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="agregarArea('areasOrden1')">Agregar Área</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="agregarOrden()">Agregar Orden</button>
                    </div>
                </div>

                <!-- Botón de Guardar Contrato -->
                <div class="row mb-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de Contratos, Órdenes y Áreas -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>RUC</th>
                            <th>Productos</th>
                            <th>Órdenes</th>
                            <th>Áreas</th>
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
                                <ul>
                                    <li>Orden 1</li>
                                    <li>Orden 2</li>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <li>Orden 1:
                                        <ul>
                                            <li>Área 1 - 50</li>
                                            <li>Área 2 - 30</li>
                                        </ul>
                                    </li>
                                    <li>Orden 2:
                                        <ul>
                                            <li>Área 3 - 20</li>
                                            <li>Área 4 - 10</li>
                                        </ul>
                                    </li>
                                </ul>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning">Editar</button>
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Lógica para agregar órdenes
    function agregarOrden() {
        const ordenesContrato = document.getElementById('ordenesContrato');
        const nuevaOrden = `
            <div class="row mb-3 align-items-center orden">
                <div class="col-md-4">
                    <label class="form-label mb-0">Orden ${ordenesContrato.children.length + 1}</label>
                </div>
                <div class="col-md-8">
                    <!-- Áreas de la Orden -->
                    <div id="areasOrden${ordenesContrato.children.length + 1}">
                        <div class="row mb-3 align-items-center area">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Área (opcional)">
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control" placeholder="Cantidad (opcional)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarArea(this)">Eliminar</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="agregarArea('areasOrden${ordenesContrato.children.length + 1}')">Agregar Área</button>
                </div>
            </div>
        `;
        ordenesContrato.insertAdjacentHTML('beforeend', nuevaOrden);
    }

    // Lógica para agregar áreas a una orden
    function agregarArea(contenedorId) {
        const contenedorAreas = document.getElementById(contenedorId);
        const nuevaArea = `
            <div class="row mb-3 align-items-center area">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Área (opcional)">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" placeholder="Cantidad (opcional)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarArea(this)">Eliminar</button>
                </div>
            </div>
        `;
        contenedorAreas.insertAdjacentHTML('beforeend', nuevaArea);
    }

    // Lógica para eliminar áreas
    function eliminarArea(boton) {
        const area = boton.closest('.area');
        area.remove();
    }

    // Lógica para manejar el formulario (puedes agregar validaciones o envío de datos)
    document.getElementById('formContrato').addEventListener('submit', function(event) {
        event.preventDefault();
        alert("Registro de contrato guardado correctamente.");
    });
</script>
@endsection