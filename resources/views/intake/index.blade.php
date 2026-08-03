@extends('template.index')

@section('header')
<h1>Consumos</h1>
<p>Lista de Consumos</p>
@endsection
@section('content')
<div class="container-fluid content-inner mt-n5 py-0"">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <form>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="producto" class="col-sm-3 col-form-label text-start">Cliente</label>
                                    <select class="form-select border-dark" aria-label="Default select example">
                                        <option selected>Seleccione</option>
                                        <option value="1">OGSS</option>
                                        <option value="2">MPR</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="producto" class="col-sm-3 col-form-label text-start">Cliente</label>
                                    <select class="form-select border-dark" aria-label="Default select example">
                                        <option selected>Seleccione</option>
                                        <option value="1">OGSS</option>
                                        <option value="2">MPR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Contrato</th>
                                    <th scope="col">Mes</th>
                                    <th scope="col">Año</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Saldo</th>
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