@extends('template.index')

@section('header')
<h1>Almacenamiento de Produccion</h1>
<p>Lista de Registros de Almacenamiento de Produccion</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title w-100">
                        <!-- Botón para crear un nuevo registro -->

                        <!-- Lista de Registros de Almacenamiento -->
                        <h4 class="mt-5">Registros de Almacenamiento</h4>
                        <div class="table-responsive">
                            <table class="table table-striped" id="storageTable">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($storages as $storage)
                                    <tr>
                                        <td>{{ $storage->finishedProduct->nombre ?? 'Sin producto' }}</td>
                                        <td>{{ $storage->quantity }}</td>
                                        <td>
                                            <!-- Botón para ver detalles -->
                                            <a href="" class="btn btn-primary btn-sm btn-icon" title="Ver Detalle">
                                                <i class="ti ti-list"></i>
                                            </a>

                                            <!-- Botón para editar -->
                                            <a href="" class="btn btn-warning btn-sm btn-icon" title="Editar">
                                                <i class="ti ti-edit"></i>
                                            </a>

                                            <!-- Botón para eliminar -->
                                            <form action="" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este registro?')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $storages->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection