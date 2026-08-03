@extends('template.index')

@section('header')
<h1>Cuentas</h1>
<p>Lista de cuentas</p>
@endsection
@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <form>
                            

                            

                            <div class="d-flex justify-content-end">
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
                                    <th>Producto</th>
                                    <th>Marca</th>
                                    <th>Tipo</th>
                                    <th>Categoría Interna</th>
                                    <th>Unidad de Medida</th>
                                    <th>Precio</th>
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