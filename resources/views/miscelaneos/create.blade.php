@extends('template.index')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('miscelaneo.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('miscelaneo.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('styles')
<style>
#btnImportar{
    margin:0 10px !important;
}
</style>
@endsection

@section('header')
<h2>Registrar productos misceláneos</h2>
<p>Complete el formulario para registrar nuevos productos misceláneos.</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Formulario de registro -->
                        <form id="createInsumoForm" action="{{ route('miscelaneo.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="category_id" value="4">
                            <div class="mb-3 row">
                                <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" id="btn-save">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
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
</style>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>

    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createInsumoForm');
    const buttonFiltrar = document.getElementById('btn-save');
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


    $('#createInsumoForm').on('submit', function(e) {
        e.preventDefault();

        let provider = [];


        // Enviar al backend
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                nombre: $('#nombre').val(),
                category_id: 5,
                unidad_medida: 'unidad',
            },
            success: function(response) {
                if (response.status) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Operación exitosa'
                    }).then(() => {
                        window.location.href = '{{ route('miscelaneo.index') }}';
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
