@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
    <x-nav-sales />
@endif
@endsection

@section('header')
<h2>Cuadre de stock materiales</h2>
<p>Ingrese el stock final y verifique el cuadre.</p>
@endsection

@section('content')
<style>
.table th:nth-child(1),
.table td:nth-child(1),
.table th:nth-child(2),
.table td:nth-child(2),
.table th:nth-child(5),
.table td:nth-child(5),
.table th:nth-child(6),
.table td:nth-child(6) {
    border-right: 3px solid rgb(207, 211, 220);
}

.table-responsive {
    max-height: 400px;
    overflow-y: auto;
    position: relative;
}

#paloteoTable {
    width: 100%;
    border-collapse: separate;
}

#paloteoTable thead th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
}

#paloteoTable th:nth-child(6),
#paloteoTable td:nth-child(6) {
    width: 200px;
    min-width: 200px;
    max-width: 200px;
}
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                            <form action="{{ route('stockMaterial.create') }}">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Producto</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="busquedaProducto" placeholder="Buscar producto...">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha inicial</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" name="start_date" value="{{ request()->start_date }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha final</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" name="end_date" value="{{ request()->end_date }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-warning w-100">Filtrar fechas</button>
                                    </div>
                                
                                </div>
                            </form>
                            <hr class="my-2">

                        <form id="createPaloteoForm" action="{{ route('stock.store') }}" method="POST">
                            @csrf
                            <div class="table-responsive
                            @if(!(request()->start_date && request()->end_date)) 
                            d-none
                            @endif
                            ">
                                <table class="table table-striped" id="paloteoTable">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>SI</th>
                                            <th>Compra</th>
                                            <th width="0"></th>
                                            <th>Salidas</th>
                                            <th>SF</th>
                                            @unless (auth()->user()->hasRole('delivery') || auth()->user()->hasRole('adminSede'))
                                                <th>Stock T</th>
                                            @endunless
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                        <tr class="fila-producto" data-product="{{ $product->id }}" data-precio="{{ $product->unit_price }}">
                                            <td>{{ $product->nombre }}</td>

                                            <td id="stock_inicial-{{ $product->id }}">
                                                {{ $stockIniciales[$product->id] ?? 0 }}
                                            </td>
                                            <td id="compra-{{ $product->id }}">
                                                {{ $ingresosCompraPorProducto[$product->id] ?? 0 }}
                                            </td>
                                            <td id="egreso-{{ $product->id }}">
                                                {{-- abs($ingresosGastoPorProducto[$product->id] ?? 0) --}}
                                            </td>
                                            <td id="salidas-{{ $product->id }}">
                                                {{ $movimientosPorProducto[$product->id] ?? 0 }}
                                            </td>

                                            <td>
                                                <input type="number"
                                                    class="form-control stock-final-input"
                                                    data-product-id="{{ $product->id }}"
                                                    data-venta-teorica="{{ $ventaTeoricaPorProducto[$product->id] ?? 0 }}"
                                                    id="stock-final-input-{{ $product->id }}">
                                            </td>

                                            <td id="stock-teorico-{{ $product->id }}">
                                                {{ $stockTeoricoPorProducto[$product->id] ?? 0 }}
                                            </td>
                                            <td class="text-center p-0" id="warning-{{ $product->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red"
                                                    class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16"
                                                    data-toggle="tooltip" data-placement="top" title="Descuadre entre venta teórica y real">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2" />
                                                </svg>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end
                            @if(!(request()->start_date && request()->end_date)) 
                            d-none
                            @endif
                            ">
                                <button type="submit" class="btn btn-primary">Guardar</button>
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
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    function activarCalculoPorProducto(productId) {
        const fila = document.querySelector(`.fila-producto[data-product="${productId}"]`);
        const stockFinalInput = document.getElementById(`stock-final-input-${productId}`);
        const stockTeoricoLabel = document.getElementById(`stock-teorico-${productId}`);
        const warning = document.getElementById(`warning-${productId}`);

        function calcularStock() {
            const stockFinalValue = parseFloat(stockFinalInput.value) || 0;
            const stockTeorico = parseFloat(stockTeoricoLabel.innerText) || 0;
            if (stockFinalValue === stockTeorico) { //puede fallar en algunos casos por el float
                warning.style.display = "none";
            } else {
                warning.style.display = "";
            }
        }

        // Ejecutar cálculo inicial
        calcularStock();
        
        // Recalcular cuando cambie el stock final
        stockFinalInput.addEventListener("input", calcularStock);
    }

    document.addEventListener('DOMContentLoaded', function() {
        @foreach($products as $product)
            activarCalculoPorProducto({{$product -> id}});
        @endforeach

        // Activar tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });

    $(document).ready(function() {
        $('#busquedaProducto').on('keyup', function() {
            var valor = $(this).val().toLowerCase();
            $('#paloteoTable tbody tr').each(function() {
                var nombre = $(this).find('td').eq(0).text().toLowerCase();
                if (nombre.includes(valor) || valor === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    $('#createPaloteoForm').submit(function(e){
        e.preventDefault();

        // Arrays para cada campo
        let product_id = [];
        let stock_inicial = [];
        let stock_final = [];
        let venta_teorica = [];

        $('#paloteoTable tbody tr').each(function(){
            let stockFinalVal = $(this).find('input.stock-final-input').val();
            if(stockFinalVal !== "" && stockFinalVal !== null && !isNaN(stockFinalVal)) {
                product_id.push($(this).data('product'));
                stock_inicial.push(parseInt($(this).find('#stock_inicial-' + $(this).data('product')).text()) || 0);
                stock_final.push(parseInt($(this).find('input.stock-final-input').val()) || 0);
                venta_teorica.push(parseInt($(this).find('#stock-teorico-' + $(this).data('product')).text()) || 0);// en paloteo materiales en la venta teorica se almacena el stock calculado
            }
        });


        $.ajax({
            url: "{{ route('stockMaterial.store') }}",
            type: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                product_id: product_id,
                stock_inicial: stock_inicial,
                stock_final: stock_final,
                venta_teorica: venta_teorica,
            },
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function(response) {
                if(response.success){

                    ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Operación exitosa'
                    }).then(() => {
                        window.location.href = window.location.pathname;
                    });
                    // Si quieres resetear el formulario:
                    // resetFormulario();
                } else {
                    ToastError.fire({
                        text: 'Ocurrió un error'
                    });
                    console.log(response.message);
                }
            },
            error: function(xhr) {
                let msg = 'Error inesperado';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                ToastError.fire({
                    text: msg
                });
                console.console.log(response.message);
            }
        });
    });

</script>
@endsection