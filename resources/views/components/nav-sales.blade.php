@if (auth()->user()->hasRole('adminSede'))
<div class="container-fluid px-3 py-3 bg-white shadow-sm rounded w-100">
    <div class="row align-items-start align-items-lg-center">

        <!-- Botones principales -->
        <div class="col-12 col-lg-8">
            <ul class="nav flex-wrap">
                @if (auth()->user()->hasRole('delivery') || auth()->user()->hasRole('adminSede'))
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.index') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.index') }}">Punto de venta</a>
                </li>
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.anticipated') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.anticipated') }}">Ventas anticipadas</a>
                </li>
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.historico') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.historico') }}">Histórico</a>
                </li>
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.detalles') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.detalles') }}">Detalles</a>
                </li>
                @endif

                @if (auth()->user()->hasRole('adminSede') && auth()->user()->isSedeRestaurante())
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.restaurante') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.restaurante') }}">Restaurante</a>
                </li>
                @endif

                @if (auth()->user()->isXinergia())
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.delete') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.delete') }}">Anulados</a>
                </li>
                @endif
            </ul>
        </div>

        <!-- Dropdowns personalizados -->
        <div class="col-12 col-lg text-lg-end text-center mt-2 mt-lg-0">
            <ul class="nav justify-content-lg-end justify-content-center flex-wrap">

                <!-- Movimientos -->
                <li class="nav-item position-relative me-2 mb-2">
                    <a href="#" class="nav-link btn btn-outline-secondary w-100"
                        onclick="toggleDropdown(event, 'dropdownMovimientos')">
                        Movimientos ▾
                    </a>
                    <ul class="dropdown-custom-menu text-start" id="dropdownMovimientos">
                        <li><a class="dropdown-item" href="{{ route('transformations.create') }}">Transformación</a></li>
                        <li><a class="dropdown-item" href="{{ route('transfers.create') }}">Traslado</a></li>
                        <li><a class="dropdown-item" href="{{ route('waste.index') }}">Merma</a></li>
                        <li><a class="dropdown-item" href="{{ route('retouch.index') }}">Retoque</a></li>
                        <li><a class="dropdown-item" href="{{ route('stock.create') }}">Paloteo</a></li>
                        <li><a class="dropdown-item" href="{{ route('expenses.cash')}}">Egresos</a></li>
                        <li><a class="dropdown-item" href="{{ route('production.dia') }}">Producción</a></li>
                    </ul>
                </li>

                <!-- Reportes -->
                <li class="nav-item position-relative me-2 mb-2">
                    <a href="#" class="nav-link btn btn-outline-secondary w-100"
                        onclick="toggleDropdown(event, 'dropdownReportes')">
                        Reportes ▾
                    </a>
                    <ul class="dropdown-custom-menu text-start" id="dropdownReportes">
                        <li><a class="dropdown-item" href="{{ route('transformations_report.index') }}">Transformación</a></li>
                        <li><a class="dropdown-item" href="{{ route('transfers_report.index') }}">Traslado</a></li>
                        <li><a class="dropdown-item" href="{{ route('waste_report.index') }}">Merma</a></li>
                        <li><a class="dropdown-item" href="{{ route('retouch_report.index') }}">Retoque</a></li>
                        <li><a class="dropdown-item" href="{{ route('stock.index') }}">Paloteo</a></li>
                        <li><a class="dropdown-item" href="{{ route('expenses.historycash')}}">Egresos</a></li>
                        <li><a class="dropdown-item" href="{{ route('payment.cashClose') }}">Cierre de caja</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>

<!-- ESTILOS -->
<style>
    .dropdown-custom-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.5rem;
        background-color: white;
        border: 1px solid #ddd;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
        z-index: 1050;
        min-width: 200px;
        list-style: none;
    }

    .dropdown-custom-menu .dropdown-item {
        padding: 8px 16px;
        display: block;
        color: #212529;
        text-decoration: none;
    }

    .dropdown-custom-menu .dropdown-item:hover {
        background-color: #f8f9fa;
    }
</style>

<!-- JAVASCRIPT -->
<script>
    function toggleDropdown(event, id) {
        event.preventDefault();
        document.querySelectorAll('.dropdown-custom-menu').forEach(menu => {
            if (menu.id !== id) {
                menu.style.display = 'none';
            }
        });
        const dropdown = document.getElementById(id);
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item')) {
            document.querySelectorAll('.dropdown-custom-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
</script>

@else
<div class="container-fluid px-3 py-3 bg-white shadow-sm rounded w-100">
    <div class="row align-items-start align-items-lg-center">
        <div class="col-12">
            <ul class="nav justify-content-center flex-wrap">
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.index') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.index') }}">Punto de venta</a>
                </li>
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.anticipated') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.anticipated') }}">Ventas anticipadas</a>
                </li>
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.historico') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.historico') }}">Histórico</a>
                </li>
                @if (auth()->user()->hasRole('admin'))
                @endif
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.detalles') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.detalles') }}">Detalles</a>
                </li>
                @if (auth()->user()->hasRole('admin') || (auth()->user()->hasRole('adminSede') && auth()->user()->isSedeRestaurante()))
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.restaurante') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.restaurante') }}">Restaurante</a>
                </li>
                @endif
                @if (auth()->user()->hasRole('delivery'))
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('payment.cashClose') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('payment.cashClose') }}">Cierre</a>
                </li>
                @endif
                @if (auth()->user()->hasRole('Xinergia') || auth()->user()->isXinergia())
                <li class="nav-item me-2 mb-2">
                    <a class="nav-link btn {{ request()->routeIs('sales.delete') ? 'btn-primary active' : 'btn-outline-primary' }} w-100"
                        href="{{ route('sales.delete') }}">Anulados</a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endif