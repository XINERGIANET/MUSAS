@extends('template.index')

@section('header')
<h1>Creditos</h1>
<p>Lista de creditos</p>
@endsection
@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <form>
                            <div class="mb-3 row">
                                <label for="codigo_interno" class="col-sm-3 col-form-label text-start">Código Interno</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="codigo_interno">
                                </div>
                                <label for="fecha" class="col-sm-3 col-form-label text-start">Fecha</label>
                                <div class="col-sm-3">
                                    <input type="date" class="form-control border-dark" id="fecha">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="cliente" class="col-sm-3 col-form-label text-start">Cliente</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="cliente">
                                </div>
                                <label for="num_orden" class="col-sm-3 col-form-label text-start">N° de orden</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="num_orden">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="placa" class="col-sm-3 col-form-label text-start">Placa vehicular</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="placa">
                                </div>
                                <label for="estado" class="col-sm-3 col-form-label text-start">Estado de cuenta</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="estado">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="galones_gasolina" class="col-sm-3 col-form-label text-start">Galones (Gasolina Regular)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="galones_gasolina" step="0.01" min="0">
                                </div>
                                <label for="galones_diesel" class="col-sm-3 col-form-label text-start">Precio (Gasolina Regular)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="precio_gasolina" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="gasolina_premium" class="col-sm-3 col-form-label text-start">Galones (Diesel B5 S50)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="gasolina_premium" step="0.01" min="0">
                                </div>
                                <label for="galones_diesel" class="col-sm-3 col-form-label text-start">Precio (Diesel B5 S50)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="precio_premiun" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="gasolina_premium" class="col-sm-3 col-form-label text-start">Galones (Gasolina Premium)</label>
                                <div class="col-sm-3">
                                    <input type="number" class="form-control border-dark" id="gasolina_premium" step="0.01" min="0">
                                </div>
                                <label for="placa" class="col-sm-3 col-form-label text-start">Precio (Gasolina Premium)</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="placa">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <label for="total">Total (General)</label>
                                <input type="number" class="form-control w-auto" id="total" step="0.01" min="0" placeholder="S/. 0.00" disabled>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-striped" data-toggle="data-table">
                            <thead>
                                <tr>
                                    <th scope="col">Código Interno</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">N° de Orden</th>
                                    <th scope="col">Galones Gasolina Regular</th>
                                    <th scope="col">Galones Diesel B5 S50</th>
                                    <th scope="col">Gasolina Premium</th>
                                    <th scope="col">Placa Vehicular</th>
                                    <th scope="col">Total S/. (General)</th>
                                    <th scope="col">Estado de Cuenta</th>
                                </tr>
                            </thead>

                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Script validacion precio -->
<script>
    document.querySelector("form").addEventListener("submit", function(event) {
        var precio = document.getElementById("precio").value;
        var regex = /^\d+(\.\d{1,2})?$/; // Expresión regular para validar números con hasta 2 decimales
        if (!regex.test(precio)) {
            event.preventDefault(); // Prevenir el envío del formulario si no es un valor válido
            alert("Por favor, ingresa un precio válido con hasta dos decimales.");
        }
    });
</script>