@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('raw_materials.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('raw_materials.index') }}">Historico</a>
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
<h2>Registrar Materia Prima</h2>
<p>Complete el formulario para registrar nueva materia prima.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createRawMaterialForm" action="{{ route('raw_materials.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="category_id" value="1">
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">Categoria (opcional)</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="product_categorie_id" name="product_categorie_id">
                                        <option value="">Seleccione una Categoria</option>
                                        @foreach ($productCategory as $category)
                                        <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label text-start">Unidad de Medida</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="unidad_medida" name="unidad_medida" required>
                                        <option value="">Seleccione una unidad de medida</option>
                                        @foreach ($unidadMedidas as $unidadMedida)
                                        <option value="{{ $unidadMedida->nombre }}">{{ $unidadMedida->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="unit_price" class="col-sm-3 col-form-label text-start">Precio Unitario</label>
                                <div class="col-sm-3">
                                    <input type="number" step="0.01" class="form-control border-dark" id="unit_price" name="unit_price" required>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-start">
                                <!-- Columna de búsqueda -->
                                <div class="col-md-5">
                                    <label class="form-label">Buscar proveedor</label>
                                    <div style="position: relative;">
                                        <input type="text" id="search-supplier" class="form-control" placeholder="Buscar proveedor..." autocomplete="off">
                                        <ul id="supplier-suggestions" class="list-group" style="position:absolute; z-index:999; width:100%; display:none;"></ul>
                                    </div>
                                </div>

                                <!-- Columna de tabla de proveedores -->
                                <div class="col-md-7">
                                    <label class="form-label">Proveedores seleccionados</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-xs" id="suppliers-table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>Proveedor</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="suppliers-table-body"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-info" type="button" id="btnImportar">
                                    <i class="bi bi-file-earmark-excel"></i> Importar
                                </button>
                                <button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importForm" method="POST">
                @csrf
                <input type="hidden" name="category_id" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Importar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><a href="{{ asset('assets/xlsx/PlantillaProductos.xlsx') }}" target="_blank">Descargar plantilla de ejemplo</a></p>
                    <div class="mb-3">
                        <label class="form-label">Archivo de plantilla *</label>
                        <input type="file" class="form-control" name="file" id="file" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
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
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('importForm');
    const buttonFiltrar = document.getElementById('btnGuardar');
    const spinner = document.getElementById('global-spinner');

    let clickedFiltrar = false;

    spinner.classList.remove('spinner-visible');
    spinner.classList.add('spinner-hidden');

    buttonFiltrar.addEventListener('click', function () {
        clickedFiltrar = true;
    });

    form.addEventListener('submit', function () {
        if (clickedFiltrar) {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        }

        clickedFiltrar = false;
    });
});

    var suppliers = @json($suppliers);

    $('#importForm').submit(function(e) {
        e.preventDefault();

        var fd = new FormData();

        fd.append('file', $('#file')[0].files[0]);
        fd.append('category_id', $('input[name="category_id"]').val());

        $.ajax({
            url: '{{ route('products.import') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            processData: false,
            contentType: false,
            data: fd,
            success: function(data) {
                if (data.status) {
                    $('#importModal').modal('hide');
                    $('#importForm')[0].reset();

                    ToastMessage.fire({
                            text: 'Archivo importado'
                        })
                        .then(() => location.reload());
                } else {
                    ToastError.fire({
                        text: data.error ? data.error : 'Ocurrió un error'
                    });
                }
            },
            error: function(err) {
                ToastError.fire({
                    text: 'Ocurrió un error'
                });
                console.log(err)
            }
        });

    });

    $('#search-supplier').autocomplete({
        source: function(request, response) {
            var results = $.map(suppliers, function(item) {
                if (
                    item.razon_social.toLowerCase().includes(request.term.toLowerCase())
                ) {
                    return {
                        label: item.razon_social,
                        value: item.razon_social,
                        id: item.id
                    };
                }
            });
            response(results);
        },
        select: function(event, ui) {
            addSupplierToTable(ui.item.id, ui.item.label);
            $('#search-supplier').val(''); // Limpia el campo de búsqueda
            return false; // evita que el valor vuelva a escribirse automáticamente
        },
        appendTo: '.container-fluid'
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };
    
    function addSupplierToTable(id, name) {
        // Verifica si ya está agregado
        if ($(`#supplier-row-${id}`).length) {
            ToastMessage?.fire({
                icon: 'warning',
                text: 'Este proveedor ya fue agregado'
            });
            return;
        }

        const row = `
            <tr id="supplier-row-${id}">
                <td>
                    ${name}
                    <input type="hidden" name="suppliers[]" value="${id}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSupplier(${id})">X</button>
                </td>
            </tr>
        `;

        $('#suppliers-table-body').append(row);
    }

    // Función global para eliminar proveedor
    function removeSupplier(id) {
        $(`#supplier-row-${id}`).remove();
    }


    $('#createRawMaterialForm').on('submit', function(e) {
        e.preventDefault();

        let provider = [];

        // ✅ CORREGIDO: usar la nueva tabla de proveedores
        $('#suppliers-table-body tr').each(function () {
            let id = $(this).attr('id')?.replace('supplier-row-', ''); // Obtener el id real

            if (id) {
                provider.push({
                    supplier_id: id // Agregar al array
                });
            }
        });

        // Validar que haya al menos uno
        if (provider.length === 0) {
            ToastMessage.fire({
                icon: 'warning',
                text: 'Debe agregar al menos 1 proveedor'
            });
            return;
        }

        // Enviar al backend
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                nombre: $('#nombre').val(),
                category_id: $('input[name="category_id"]').val(),
                product_categorie_id: $('#product_categorie_id').val(),
                unidad_medida: $('#unidad_medida').val(),
                unit_price: $('#unit_price').val(),
                provider: JSON.stringify(provider) // ← Este es tu array de proveedores
            },
            success: function(response) {
                if (response.status) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Operación exitosa'
                    }).then(() => {
                        window.location.href = '{{ route('raw_materials.index') }}';
                    });
                } else {
                    ToastError.fire({
                        text: response.error || 'Ocurrió un error'
                    });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    const errorMessages = Object.values(errors).join('\n');
                    alert(errorMessages);
                } else {
                    ToastError.fire({
                        text: xhr.responseJSON?.message || 'Ocurrió un error'
                    });
                }
            }
        });
    });
</script>
@endsection