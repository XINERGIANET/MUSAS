<!doctype html>
<html lang="es" dir="ltr" translate="no">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Musas</title>
    <!-- Favicon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" /> -->
    <link rel="icon" href="{{ asset('assets/icon/logo.svg') }}" type="image/svg+xml" />

    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />

    <!-- Aos Animation Css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/aos/dist/aos.css') }}" />

    <!-- Hope Ui Design System Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css') }}" />

    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css') }}" />

    <!-- Dark Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />

    <!-- Customizer Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />

    <!-- RTL Css -->
    <link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2-theme-material-ui.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}" />

    <style>
        #notificaciones-body {
            max-height: 300px;
            /* Ajusta la altura máxima según tus necesidades */
            overflow-y: auto;
            /* Habilita el scroll vertical */
        }
    </style>
    @yield('styles')
</head>

<body class="">
    <!-- loader Start -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>
    <!-- loader END -->
    @if(auth()->check() && !(auth()->user()->hasRole('adminSede') || auth()->user()->hasRole('delivery')))
    <aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all ">
        <div class="sidebar-header d-flex align-items-center justify-content-start">
            <a href="{{ route('reports.index')}}" class="navbar-brand">
                <!--Logo start-->
                <!-- <div class="logo-main">
                    <div class="logo-normal">
                        <svg class=" icon-30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="logo-mini">
                        <svg class=" icon-30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                </div> -->
                <div class="logo-main">
                    <div class="logo-normal">
                        <img src="{{ asset('assets/icon/logo.svg') }}" alt="Logo Normal" class="icon-30">
                    </div>
                    <div class="logo-mini">
                        <img src="{{ asset('assets/icon/logo.svg') }}" alt="Logo Mini" class="icon-30">
                    </div>
                </div>

                <!--logo End-->
                <h4 class="logo-title">Musas</h4>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </i>
            </div>
        </div>
        <div class="sidebar-body pt-0 data-scrollbar">
            <div class="sidebar-list">
                <!-- Sidebar Menu Start -->
                <ul class="navbar-nav iq-main-menu" id="sidebar-menu">

                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia')))
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('reports.index') }}">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">Indicadores</span>
                        </a>
                    </li>
                    

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('sales.index') }}">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <path d="M4 7V6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V7" stroke="currentColor" stroke-width="1.5" />
                                    <rect x="3" y="7" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M9 10H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M9 14H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                            </i>
                            <span class="item-name">Punto de Venta</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('payment.cashClose') }}">
                            <i class="bi bi-cash-stack"></i>
                            <span class="item-name">Cierre de caja</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-special" role="button" aria-expanded="false" aria-controls="sidebar-special">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 8L12 3L3 8V18L12 23L21 18V8ZM12 5.3L18 8.6L12 12L6 8.6L12 5.3ZM5 10.1L11 13.8V20.7L5 17.3V10.1ZM13 20.7V13.8L19 10.1V17.3L13 20.7Z" fill="currentColor" />
                                </svg>
                            </i>

                            <span class="item-name">Base de Datos</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-special" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('payment_methods.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> M </i>
                                    <span class="item-name">Métodos de pago</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route ('headquarters.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> S </i>
                                    <span class="item-name">Sede</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('puestos.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> P </i>
                                    <span class="item-name">Puestos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('staff.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> P </i>
                                    <span class="item-name">Personal</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('suppliers.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> P </i>
                                    <span class="item-name">Proovedores</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('usuarios.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> U </i>
                                    <span class="item-name">Usuarios</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('unidad_medidas.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> U </i>
                                    <span class="item-name">Unidades de Medida</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route ('category.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> C </i>
                                    <span class="item-name">Categoría</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route ('miscelaneo.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> M </i>
                                    <span class="item-name">Misceláneos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route ('insumos.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> I </i>
                                    <span class="item-name">Insumos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('raw_materials.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> M </i>
                                    <span class="item-name">Materia Prima</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('finished_products.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> P </i>
                                    <span class="item-name">Productos Industrializados</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route ('products.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> P </i>
                                    <span class="item-name">Productos Finalizados</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('clients.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> C </i>
                                    <span class="item-name">Clientes</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia')))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-maps" role="button" aria-expanded="false" aria-controls="sidebar-maps">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 11.5V5C21 3.9 20.1 3 19 3H12.5C12.2 3 11.9 3.1 11.7 3.3L3.3 11.7C3.1 11.9 3 12.2 3 12.5V19C3 20.1 3.9 21 5 21H11.5C11.8 21 12.1 20.9 12.3 20.7L20.7 12.3C20.9 12.1 21 11.8 21 11.5ZM17 7C17.6 7 18 7.4 18 8C18 8.6 17.6 9 17 9C16.4 9 16 8.6 16 8C16 7.4 16.4 7 17 7ZM5 12.9L12.9 5H19V11.1L11.1 19H5V12.9Z" fill="currentColor" />
                                </svg>
                            </i>
                            <span class="item-name">Compras</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-maps" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('purchases.create', ['tipo' => 'compra']) }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> C </i>
                                    <span class="item-name">Compras</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('purchases.create', ['tipo' => 'egreso']) }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> EG </i>
                                    <span class="item-name">Egresos Generales</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('purchases.index', ['tipo' => 'egresoSede']) }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> ES </i>
                                    <span class="item-name">Egresos por Sede</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia')))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-production" role="button" aria-expanded="false" aria-controls="sidebar-production">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <!-- Fábrica -->
                                    <path d="M3 21V10L8 13V10L13 13V10L21 14V21H3Z"
                                        stroke="currentColor" stroke-width="1.5" />
                                    <path d="M7 21V17H10V21"
                                        stroke="currentColor" stroke-width="1.5" />
                                    <path d="M14 21V17H17V21"
                                        stroke="currentColor" stroke-width="1.5" />
                                    <!-- Engranaje -->
                                    <path d="M12 5.5C12.8284 5.5 13.5 6.17157 13.5 7C13.5 7.82843 12.8284 8.5 12 8.5C11.1716 8.5 10.5 7.82843 10.5 7C10.5 6.17157 11.1716 5.5 12 5.5Z"
                                        stroke="currentColor" stroke-width="1.5" />
                                    <path d="M12 2V3.5M12 10.5V12M15.5 7H17M7 7H8.5M14.1213 9.87868L15.182 10.9393M8.81796 3.06066L9.87868 4.12132M9.87868 9.87868L8.81796 10.9393M15.182 3.06066L14.1213 4.12132"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                            </i>
                            <span class="item-name">Producción</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-production" data-bs-parent="#sidebar-production">
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('production.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> DIR </i>
                                    <span class="item-name">Directas</span>
                                </a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('production.personalized') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> ANT </i>
                                    <span class="item-name">Anticipadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('production.delivery') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> DEL </i>
                                    <span class="item-name">Delivery</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-widget" role="button" aria-expanded="false" aria-controls="sidebar-widget">

                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C10.9 2 10 2.9 10 4V5H7C5.9 5 5 5.9 5 7V17C5 18.1 5.9 19 7 19H17C18.1 19 19 18.1 19 17V7C19 5.9 18.1 5 17 5H14V4C14 2.9 13.1 2 12 2ZM12 4C12.6 4 13 4.4 13 5V6H11V5C11 4.4 11.4 4 12 4ZM7 7H17V17H7V7ZM15 10H9V14H15V10Z" fill="currentColor" />
                                </svg>
                            </i>
                            <span class="item-name">Almacenes</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-widget" data-bs-parent="#sidebar-menu">
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('storageInsumo.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> AI </i>
                                    <span class="item-name">Almacén de<br> Insumos</span>
                                </a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('storage1.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> AMP </i>
                                    <span class="item-name">Almacén de <br>materia prima</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('storage2.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> API </i>
                                    <span class="item-name">Almacén de <br>productos indust.</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('consumption.create')}}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> SAL </i>
                                    <span class="item-name">Salidas de almacén</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('ingresos.create')}}?categoria=insumos">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon">SALI</i>
                                    <span class="item-name">Salidas de Insumos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{route('ingresos.create')}}?categoria=industrializados">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon">SALI</i>
                                    <span class="item-name">Salidas de Ind.</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('storage4.index') }}">
                            <i class="icon">
                                 <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <!-- Techo del almacén -->
                                    <path d="M3 10L12 4L21 10V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V10Z" stroke="currentColor" stroke-width="1.5"/>
                                    <!-- Puerta enrollable -->
                                    <rect x="7" y="14" width="10" height="4" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M7 14V10H17V14" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </i>
                            <span class="item-name">Almacén por Sede</span>
                        </a>
                    </li>
                    @endif
                    
                    @if(auth()->check() && !auth()->user()->hasRole('delivery'))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#sidebar-movements" role="button" aria-expanded="false" aria-controls="sidebar-widget">

                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5" fill="currentColor" />
                                </svg>
                            </i>
                            <span class="item-name">Movimientos</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-movements" data-bs-parent="#sidebar-menu">
                            <li class="nav-item" style="display: none;">
                                <a class="nav-link " href="{{ route('stock.inicial') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> SI </i>
                                    <span class="item-name">Stock Inicial</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('transformations.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> TRN </i>
                                    <span class="item-name">Transformación</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('transfers.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> TRS </i>
                                    <span class="item-name">Traslado</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('waste.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> MRM </i>
                                    <span class="item-name">Merma</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('retouch.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> RTQ </i>
                                    <span class="item-name">Retoque</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('stock.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> PLT </i>
                                    <span class="item-name">Paloteo Productos</span>
                                </a>
                            </li>

                             <li class="nav-item">
                                <a class="nav-link " href="{{ route('stockMaterial.create') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> PLT </i>
                                    <span class="item-name">Paloteo Materiales</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('Xinergia')))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebar-widget" href="#sidebar-reports">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 4H20V6H4V4ZM4 8H20V10H4V8ZM4 12H14V14H4V12ZM4 16H14V18H4V16ZM4 20H20V22H4V20Z" fill="currentColor" />
                                </svg>
                            </i>
                            <span class="item-name">Reportes</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                        <ul class="sub-nav collapse" id="sidebar-reports" data-bs-parent="#sidebar-menu">
                        <li class="nav-item">
                                <a class="nav-link " href="{{ route('transformations_report.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> TRANS </i>
                                    <span class="item-name">Transformación</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('transfers_report.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> TRAS </i>
                                    <span class="item-name">Traslado</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('waste_report.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> MRM </i>
                                    <span class="item-name">Merma</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('retouch_report.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> RET </i>
                                    <span class="item-name">Retoque</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('stock.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> PLT </i>
                                    <span class="item-name">Paloteo Productos</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('stockMaterial.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> PLT </i>
                                    <span class="item-name">Paloteo Materiales</span>
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link " href="{{ route('payment.index') }}">
                                    <i class="icon">
                                        <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                            <g>
                                                <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
                                            </g>
                                        </svg>
                                    </i>
                                    <i class="sidenav-mini-icon"> PLT </i>
                                    <span class="item-name">Pagos por Mét.</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif

                    @if(auth()->check() && auth()->user()->hasRole('produccion'))
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('sales.anticipated') }}">
                            <i class="icon">
                                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                    <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"></path>
                                </svg>
                            </i>
                            <span class="item-name">Ventas anticipadas</span>
                        </a>
                    </li>
                    
                    @endif
            
                </ul>
                <!-- Sidebar Menu End -->
            </div>
        </div>
        <div class="sidebar-footer"></div>
    </aside>
    @endif

    <main class="main-content">
        <!-- Banner Section -->
        <div class="position-relative iq-banner">
            <!-- Navigation Start -->
            <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
                <div class="container-fluid navbar-inner">
                    <!-- Brand Logo -->
                    <a
                        @if(auth()->check() && !(auth()->user()->hasRole('adminSede') || auth()->user()->hasRole('delivery')))
                         href="{{ route('reports.index') }}"
                        @else
                        href="javascript:void(0)"
                        style="cursor: default;"
                        @endif
                        class="navbar-brand">
                        <div class="logo-main">
                            <div class="logo-normal">
                                <img src="{{ asset('assets/icon/logo.svg') }}" alt="Logo Normal" class="icon-30">
                            </div>
                            <div class="logo-mini">
                                <img src="{{ asset('assets/icon/logo.svg') }}" alt="Logo Mini" class="icon-30">
                            </div>
                        </div>
                        <h4 class="logo-title">Musas</h4>
                    </a>
                    <!-- Sidebar Toggle -->
                    @if (!(in_array(Route::currentRouteName(), ['sales.index', 'sales.anticipated']) && auth()->check() && auth()->user()->hasRole('adminSede')))
                    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                        <i class="icon">
                            <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                            </svg>
                        </i>
                    </div>
                    @endif

                    <!-- Navbar Toggler -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon">
                            <span class="mt-2 navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </span>
                    </button>

                    <!-- Navbar Content -->
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto align-items-center navbar-list mb-lg-0">

                            @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('delivery') || auth()->user()->hasRole('Xinergia')))
                            @auth
                            <li class="nav-item dropdown">
                                @php
                                $sedes = \App\Models\Headquarters::where('estado', 0)->get();
                                @endphp
                                <select class="form-select" id="selectSede" name="sede" style="width:auto;">
                                    <option value=""
                                            @if(auth()->user()->headquarter == null) selected @endif>
                                            Seleccionar sede
                                        </option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}"
                                            @if(auth()->user()->headquarter && auth()->user()->headquarter->id == $sede->id) selected @endif>
                                            {{ $sede->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </li>
                            @endif
                        
                            @if (auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('xinergia') || auth()->user()->hasRole('delivery')))
                                <li class="nav-item dropdown">
                                <div class="d-flex align-items-center mx-4">
                                    <span class="form-check-label">Mañana</span>
                                    <div class="form-check form-switch mx-1" style="padding-left: 3em !important;">
                                        <input class="form-check-input" type="checkbox" role="switch" id="switchTurno"
                                        @if( auth()->user()->turno == 1 ) 
                                            checked
                                        @endif
                                        >
                                    </div>
                                    <span class="form-check-label">Tarde</span>
                                </div>
                            </li>
                        
 
                            @endif
                                                       <li class="nav-item dropdown">
                                <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown">
                                    <svg class="icon-24" width="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18.7071 8.79633C18.7071 10.0523 19.039 10.7925 19.7695 11.6456C20.3231 12.2741 20.5 13.0808 20.5 13.956C20.5 14.8302 20.2128 15.6601 19.6373 16.3339C18.884 17.1417 17.8215 17.6573 16.7372 17.747C15.1659 17.8809 13.5937 17.9937 12.0005 17.9937C10.4063 17.9937 8.83505 17.9263 7.26375 17.747C6.17846 17.6573 5.11602 17.1417 4.36367 16.3339C3.78822 15.6601 3.5 14.8302 3.5 13.956C3.5 13.0808 3.6779 12.2741 4.23049 11.6456C4.98384 10.7925 5.29392 10.0523 5.29392 8.79633V8.3703C5.29392 6.68834 5.71333 5.58852 6.577 4.51186C7.86106 2.9417 9.91935 2 11.9558 2H12.0452C14.1254 2 16.2502 2.98702 17.5125 4.62466C18.3314 5.67916 18.7071 6.73265 18.7071 8.3703V8.79633ZM9.07367 20.0608C9.07367 19.5573 9.53582 19.3266 9.96318 19.2279C10.4631 19.1222 13.5093 19.1222 14.0092 19.2279C14.4366 19.3266 14.8987 19.5573 14.8987 20.0608C14.8738 20.5402 14.5926 20.9653 14.204 21.2352C13.7001 21.628 13.1088 21.8767 12.4906 21.9664C12.1487 22.0107 11.8128 22.0117 11.4828 21.9664C10.8636 21.8767 10.2723 21.628 9.76938 21.2342C9.37978 20.9653 9.09852 20.5402 9.07367 20.0608Z" fill="currentColor"></path>
                                    </svg>
                                    @php

                                    $ind = collect();
                                    $hq = collect();

                                    if (\Illuminate\Support\Facades\Schema::hasColumns('storage2s', ['product_id', 'quantity', 'stock_minimo', 'estado'])) {
                                        $ind = App\Models\Storage2::whereHas('product', function ($query) {
                                            $query->where('products.estado', '=', 0);
                                        })
                                        ->whereColumn('quantity', '<=', 'stock_minimo')
                                        ->where('estado', 0)
                                        ->get();
                                    }

                                    if (\Illuminate\Support\Facades\Schema::hasColumns('storage3s', ['product_id', 'quantity', 'stock_minimo', 'estado'])) {
                                        $hq = App\Models\Storage3::whereHas('product', function ($query) {
                                            $query->where('products.estado', '=', 0);
                                        })
                                        ->whereColumn('quantity', '<=', 'stock_minimo')
                                        ->where('estado', 0)
                                        ->get();
                                    }

                                            $items = $ind->merge($hq);

                                            $numero = $items->count();

                                            @endphp
                                            <span class="fw-bold small">{{ $numero }}</span>
                                </a>
                                <div class="p-0 sub-drop dropdown-menu dropdown-menu-end" id="notificaciones" aria-labelledby="notification-drop">
                                    <div class="m-0 shadow-none card">
                                        <div class="py-3 card-header d-flex justify-content-between bg-primary">
                                            <div class="header-title">
                                                <h5 class="mb-0 text-white">Bajo stock</h5>
                                            </div>
                                        </div>
                                        <div class="p-0 card-body justify-content-center align-items-center" id="notificaciones-body">

                                            @if($numero == 0)
                                            <a href="#" class="iq-sub-card">
                                                No tienes notificaciones
                                            </a>
                                            @else

                                            @foreach($items as $item)
                                            <a href="" class="iq-sub-card">
                                                {{ $item->product->nombre }} ({{ $item->quantity }})
                                                <p style="margin-left:1rem"> <b> {{ $item->headquarter ? $item->headquarter->nombre : 'Central' }} </b> </p>
                                            </a>
                                            @endforeach


                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endauth

                            <li class="nav-item dropdown">
                                <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <!-- User Avatar Images -->
                                    <img src="{{ asset('assets/images/avatars/01.png') }}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
                                    <img src="{{ asset('assets/images/avatars/avtar_1.png') }}" alt="User-Profile" class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
                                    <img src="{{ asset('assets/images/avatars/avtar_2.png') }}" alt="User-Profile" class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
                                    <img src="{{ asset('assets/images/avatars/avtar_4.png') }}" alt="User-Profile" class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
                                    <img src="{{ asset('assets/images/avatars/avtar_5.png') }}" alt="User-Profile" class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
                                    <img src="{{ asset('assets/images/avatars/avtar_3.png') }}" alt="User-Profile" class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded">
                                    <!-- User Info -->
                                    <div class="caption ms-3 d-none d-md-block">
                                        <h6 class="mb-0 caption-title">
                                            @auth
                                            {{ auth()->user()->nombre }} <!-- User Name -->
                                            @else
                                            Usuario <!-- Default Text -->
                                            @endauth
                                        </h6>
                                        <p class="mb-0 caption-sub-title">
                                            @auth
                                            {{ auth()->user()->email }} <!-- User Email -->
                                            @else
                                            Invitado <!-- Default Text -->
                                            @endauth
                                        </p>
                                    </div>
                                </a>
                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
									<li>
										<form action="{{ route('logout') }}" method="POST">
											@csrf <!-- Token de seguridad -->
											<button type="submit" class="dropdown-item">Cerrar Sesión</button>
										</form>
									</li>
								</ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Navigation End -->
            <!-- Nav Header Component Start -->

            @yield('nav')
            <div class="iq-navbar-header">
                <div class="container-fluid iq-container">
                    @yield('header')
                </div>

                @if (Route::currentRouteName() !== 'sales.index')
                <div class="iq-header-img">
                    <img src="{{ asset('assets/images/dashboard/top-header.png') }}" alt="header" class="img-fluid w-100 h-100 animated-scaleX">
                </div>
                @endif
            </div>

            <!-- Nav Header Component End -->
        </div>

        <!-- Main Content Section -->

        @yield('content')
        
        <!-- TODO: quitar el spinner en c/view y hacerlo en el template, configurando el ajax globalmente -->
        <div id="global-spinner-template" class="d-flex justify-content-center align-items-center spinner-hidden"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <!-- Modal de Selección de Turno -->
        @if(auth()->check() && (auth()->user()->hasRole('adminSede') || auth()->user()->hasRole('delivery')) && !(auth()->user()->email =='ALEJANDRADELIVERY' || auth()->user()->email =='RAQUELDELIVERY' || auth()->user()->email =='JAVIERDELIVERY' || auth()->user()->email =='ROSAMUSASDEL'))
        <div class="modal fade" id="turnoModal" tabindex="-1" aria-labelledby="turnoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="turnoModalLabel">Seleccionar Turno</h5>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Por favor, selecciona tu turno de trabajo:</p>
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-outline-primary btn-turno" data-turno="0">
                                <i class="fas fa-sun me-2"></i>
                                Mañana
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-turno" data-turno="1">
                                <i class="fas fa-moon me-2"></i>
                                Tarde
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif


        <!-- Footer Section Start -->
        <footer class="footer">
            <div class="footer-body">
                <div class="right-panel">
                    ©<script>
                        document.write(new Date().getFullYear())
                    </script> Musas
                    <span></span> by <a href="">Xinergia</a>.
                </div>
            </div>
        </footer>
        <!-- Footer Section End -->
    </main>
    <!-- Wrapper End-->
    <!-- offcanvas start -->

    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

    <!-- Library Bundle Script -->
    <script src="{{ asset('assets/js/core/libs.min.js') }}"></script>

    <!-- External Library Bundle Script -->
    <script src="{{ asset('assets/js/core/external.min.js') }}"></script>

    <!-- Widgetchart Script -->
    <script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>

    <!-- mapchart Script -->
    <script src="{{ asset('assets/js/charts/vectore-chart.js') }}"></script>
    <script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>

    <!-- fslightbox Script -->
    <script src="{{ asset('assets/js/plugins/fslightbox.js') }}"></script>

    <!-- Settings Script -->
    <script src="{{ asset('assets/js/plugins/setting.js') }}"></script>

    <!-- Slider-tab Script -->
    <script src="{{ asset('assets/js/plugins/slider-tabs.js') }}"></script>

    <!-- Form Wizard Script -->
    <script src="{{ asset('assets/js/plugins/form-wizard.js') }}"></script>

    <!-- AOS Animation Plugin-->
    <script src="{{ asset('assets/vendor/aos/dist/aos.js') }}"></script>

    <!-- App Script -->
    <script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>

    <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>

    <script>
        const ToastError = Swal.mixin({
            title: 'Error',
            icon: 'error',
            toast: true,
            position: 'bottom-end',
            timer: 2500,
            timerProgressBar: true
        });

        const ToastMessage = Swal.mixin({
            title: 'Mensaje',
            icon: 'success',
            toast: true,
            position: 'bottom-end',
            timer: 1500,
            timerProgressBar: false
        });

        const ToastConfirm = Swal.mixin({
            icon: 'question',
            showDenyButton: true,
            confirmButtonText: 'Aceptar',
            denyButtonText: 'Cancelar',
            toast: true,
            position: 'bottom-end'
        });
    </script>


    @if(session('show_turno_modal'))
    <script>
        $(document).ready(function() {
            $('#turnoModal').modal('show');
            $('#turnoModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('.btn-turno').on('click', function() {
                var turno = $(this).data('turno');
                $.ajax({
                    url: "{{ route('user.setTurno') }}",
                    method: "POST",
                    data: {
                        turno: turno,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#turnoModal').modal('hide');
                            ToastMessage.fire({
                                text: 'Turno guardado correctamente.'
                            });
                            location.reload();
                        } else {
                            ToastError.fire({
                                text: 'No se pudo guardar el turno.'
                            });
                        }
                    },
                    error: function() {
                        ToastError.fire({
                            text: 'Error de conexión.'
                        });
                    }
                });
            });
        });
    </script>
    @endif

    <script>
        const spinner = document.getElementById('global-spinner-template');
        let turnoPrevio = $('#switchTurno').is(':checked') ? 1 : 0;

        $('#switchTurno').on('change', function(e) {
            var turno = $(this).is(':checked') ? 1 : 0;
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');

            $.ajax({
                url: '{{ route('user.cambiarTurno') }}',
                method: 'POST',
                data: {
                    turno: turno,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Operación exitosa'
                        }).then(() => location.reload());
                    } else {
                        ToastError.fire({
                            text: 'Ocurrió un error'
                        });
                        $this.prop('checked', turnoPrevio === 1);
                    }
                    
                },
                error: function(xhr) {
                    ToastError.fire({
                        text: 'Ocurrió un error'
                    });
                    $this.prop('checked', turnoPrevio === 1);
                }
            })
            .always(function() {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');
            });
        });

        let sedePrevia = $('#selectSede').val();

        $('#selectSede').on('focus', function() {
            sedePrevia = $(this).val();
        });


        $('#selectSede').on('change', function() {
            var sedeId = $(this).val();
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');

            $.ajax({
                url: '{{ route("user.cambiarSede") }}',
                method: 'POST',
                data: {
                    sede: sedeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Sede cambiada correctamente'
                        });
                        sedePrevia = sedeId;
                        location.reload();
                    } else {
                        ToastError.fire({
                            text: response.message || 'No se pudo cambiar la sede'
                        });
                        $('#selectSede').val(sedePrevia); 
                    }
                },
                error: function() {
                    ToastError.fire({
                        text: 'Ocurrió un error al cambiar la sede'
                    });
                    $('#selectSede').val(sedePrevia); 
                },
                complete: function() {
                    spinner.classList.add('spinner-hidden');
                    spinner.classList.remove('spinner-visible');
                }
            });
        });

    </script>
    <style>
        .spinner-hidden {
            display: none !important;
        }
        .spinner-visible {
            display: flex !important;
        }
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
        
        /* Estilos para el modal de turno */
        #turnoModal .modal-content {
            border-radius: 15px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-turno {
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
        }
        
        .btn-turno:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        
        .btn-turno i {
            font-size: 18px;
        }
        
        #turnoModal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
        }
        
        #turnoModal .modal-title {
            font-weight: 600;
            font-size: 18px;
        }
    </style>



    @yield('scripts')

</body>

</html>
