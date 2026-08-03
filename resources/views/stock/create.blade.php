@extends('template.index')

@section('nav')
@if (auth()->user()->hasRole('adminSede'))
<x-nav-sales />
@endif
@endsection

@section('header')
<h2>Cuadre de stock</h2>
<p>Ingrese el stock final y verifique el cuadre.</p>
@endsection

@section('content')
<style>
    .table th:nth-child(1),
    .table td:nth-child(1),
    .table th:nth-child(2),
    .table td:nth-child(2),
    .table th:nth-child(4),
    .table td:nth-child(4) {
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

#paloteoTable th:nth-child(4),
#paloteoTable td:nth-child(4) {
    width: 200px;
    min-width: 200px;
    max-width: 200px;
}

/* Overlay spinner para toda la vista */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.spinner-border-lg {
    width: 3rem;
    height: 3rem;
}

</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border spinner-border-lg text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <div class="mt-3">
            <h5>Guardando cuadre de stock...</h5>
            <p class="text-muted mb-0">Por favor espere</p>
        </div>
    </div>
</div>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <form id="createPaloteoForm" action="{{ route('stock.store') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Producto</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control"
                                            id="busquedaProducto"
                                            placeholder="Buscar producto...">
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped" id="paloteoTable">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Stock Inicial</th>
                                            <th>Entradas</th>
                                            <th>Salidas</th>
                                            <th>Stock Final</th>
                                            <th>Venta T</th>
                                            <th>Venta R</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                        <tr class="fila-producto" data-product="{{ $product->id }}" data-precio="{{ $product->unit_price }}">
                                            <td>{{ $product->nombre }}</td>
                                            <td id="stock_inicial-{{ $product->id }}">
                                                {{ $stockIniciales[$product->id] ?? 0 }}
                                            </td>
                                            <td id="entradas-{{ $product->id }}">
                                                {{ $entradasPorProducto[$product->id] ?? 0 }}
                                            </td>
                                            <td id="salidas-{{ $product->id }}">
                                                {{ $salidasPorProducto[$product->id] ?? 0 }}
                                            </td>
                                            <td>
                                                <input type="number"
                                                    class="form-control stock-final-input"
                                                    data-product-id="{{ $product->id }}"
                                                    id="stock-final-input-{{ $product->id }}">
                                            </td>
                                            <td id="venta-teorica-{{ $product->id }}">
                                                0
                                            </td>
                                            <td id="venta-real-{{ $product->id }}">{{ $ventasPorProducto[$product->id] ?? 0 }}</td>
                                            <td class="text-center p-0" id="warning-{{ $product->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red"
                                                    class="bi bi-exclamation-circle-fill" viewBox="0 0 16 16"
                                                    data-toggle="tooltip" data-placement="top" title="Descuadre entre venta teórica y real">
                                                    <path
                                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2" />
                                                </svg>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
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
        const precioUnitario = parseFloat(fila.getAttribute("data-precio")) || 0;

        const stockInicial = document.getElementById(`stock_inicial-${productId}`);
        const entradas = document.getElementById(`entradas-${productId}`);
        const salidas = document.getElementById(`salidas-${productId}`);
        const stockFinalInput = document.getElementById(`stock-final-input-${productId}`);
        const ventaTeorica = document.getElementById(`venta-teorica-${productId}`);
        const ventaReal = document.getElementById(`venta-real-${productId}`);
        const warning = document.getElementById(`warning-${productId}`);

        function calcularVenta() {
            const stockInicialValue = parseInt(stockInicial.innerText) || 0;
            const entradasValue = parseInt(entradas.innerText) || 0;
            const salidasValue = parseInt(salidas.innerText) || 0;
            const stockFinalValue = parseInt(stockFinalInput.value) || 0;

            // Venta teórica = Stock Inicial + Entradas - Salidas - Stock Final
            const ventaTeoricaValue = stockInicialValue + entradasValue - salidasValue - stockFinalValue;
            ventaTeorica.innerText = ventaTeoricaValue;

            // Obtener venta real (valor fijo del servidor)
            const ventaRealValue = parseInt(ventaReal.innerText) || 0;

            // Warning por descuadre entre venta calculada y venta real
            if (ventaRealValue === ventaTeoricaValue) {
                warning.style.display = "none";
            } else {
                warning.style.display = "";
            }
        }

        // Ejecutar cálculo inicial
        calcularVenta();
        
        // Recalcular cuando cambie el stock final
        stockFinalInput.addEventListener("input", calcularVenta);
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

    // Funciones para mostrar/ocultar overlay
    function mostrarOverlay() {
        $('#loadingOverlay').css('display', 'flex');
    }

    function ocultarOverlay() {
        $('#loadingOverlay').hide();
    }

    $('#createPaloteoForm').submit(function(e){
        e.preventDefault();

        // Arrays para cada campo
        let product_id = [];
        let stock_inicial = [];
        let entradas = [];
        let salidas = [];
        let stock_final = [];
        let venta_teorica = [];
        let venta_real = [];

        let productosSinStockFinal = [];

        $('#paloteoTable tbody tr').each(function(){
            let ventaRealVal = parseInt($(this).find('#venta-real-' + $(this).data('product')).text()) || 0;
            let stockFinalVal = $(this).find('input.stock-final-input').val() || -1;
            let nombreProd = $(this).find('td').eq(0).text();

            console.log('Revisando producto:', nombreProd, 'Venta Real:', ventaRealVal, 'Stock Final:', stockFinalVal);

            // Si hay venta real y no hay stock final, lo marcamos
            if(ventaRealVal > 0 && stockFinalVal == -1) {
                console.log('Producto sin stock final:', nombreProd);
                productosSinStockFinal.push(nombreProd);
            }

            if(stockFinalVal !== -1) {
                product_id.push($(this).data('product'));
                stock_inicial.push(parseInt($(this).find('#stock_inicial-' + $(this).data('product')).text()) || 0);
                entradas.push(parseInt($(this).find('#entradas-' + $(this).data('product')).text()) || 0);
                salidas.push(parseInt($(this).find('#salidas-' + $(this).data('product')).text()) || 0);
                stock_final.push(parseInt($(this).find('input.stock-final-input').val()) || 0);
                venta_teorica.push(parseInt($(this).find('#venta-teorica-' + $(this).data('product')).text()) || 0);
                venta_real.push(parseInt($(this).find('#venta-real-' + $(this).data('product')).text()) || 0);
            }
        });

        console.log(productosSinStockFinal)

        if(productosSinStockFinal.length > 0){
            ToastConfirm.fire({
                text: 'Hay productos con venta real sin stock final. ¿Desea continuar?',
            }).then((result) => {
                 if (result.isConfirmed) {
                    enviarFormulario();
                 }
            });
        } else{
            enviarFormulario();
        }

        function enviarFormulario() {
            // Mostrar overlay de carga
            mostrarOverlay();
        
            $.ajax({
                url: "{{ route('stock.store') }}",
                type: "POST",
                data: {
                    _token: $('input[name="_token"]').val(),
                    product_id: product_id,
                    stock_inicial: stock_inicial,
                    entradas: entradas,
                    salidas: salidas,
                    stock_final: stock_final,
                    venta_teorica: venta_teorica,
                    venta_real: venta_real
                },
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    ocultarOverlay();
                    
                    if(response.success){
                        ToastMessage.fire({
                                icon: 'success',
                                text: response.message || 'Operación exitosa'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        ToastError.fire({
                            text: response.message
                        });
                        console.log(response.message);
                    }
                },
                error: function(xhr) {
                    ocultarOverlay();
                    
                    let msg = 'Error inesperado';
                    if(xhr.responseJSON && xhr.responseJSON.message){
                        msg = xhr.responseJSON.message;
                    }
                    ToastError.fire({
                        text: msg
                    });
                    console.log(xhr.responseJSON);
                }
            });
        }
    });

</script>

@endsection