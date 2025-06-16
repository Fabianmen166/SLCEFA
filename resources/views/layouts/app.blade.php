<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/Favicon2.png') }}" type="image/x-icon">
    <title>Bienvenido Personal Técnico - @yield('title')</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Remove existing Navbar and add a simplified one -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <div class="container-fluid">
                <!-- Logo / Brand (optional, can be removed if not needed) -->
                <a class="navbar-brand" href="{{ route('personal_tecnico.dashboard') }}">
                    <img src="{{ asset('images/Logo.png') }}" alt="Logo" height="30">
                </a>

                <!-- Toggler for mobile (if needed for responsiveness) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto"> <!-- Using ms-auto for Bootstrap 5 right alignment -->
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Cerrar Sesión') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-success-green elevation-4">
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Eliminar <br> tags si ya no son necesarios con el nuevo navbar -->
                        <br><br><br><br>

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('personal_tecnico.dashboard') }}" class="nav-link text-success">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Análisis Técnico -->
                        <li class="nav-item">
                            <a href="{{ route('process.technical_index') }}" class="nav-link text-success">
                                <i class="nav-icon fas fa-vial"></i>
                                <p>Análisis Técnico</p>
                            </a>
                        </li>

                        <!-- Gestión de Análisis de pH -->
                        @if (Auth::user()->role === 'personal_tecnico')
                            <li class="nav-item">
                                <a href="{{ route('ph_analysis.index') }}" class="nav-link text-success">
                                    <i class="nav-icon fas fa-flask"></i>
                                    <p>Gestión de pH</p>
                                </a>
                            </li>
                        @endif
                                                
                        @if (Auth::user()->role === 'personal_tecnico')
                            <li class="nav-item">
                                <a href="{{ route('conductivity.index') }}" class="nav-link text-success">
                                    <i class="nav-icon fas fa-flask"></i>
                                    <p>Conductividad</p>
                                </a>
                            </li>
                        @endif

                        @if (Auth::user()->role === 'personal_tecnico')
                            <li class="nav-item">
                                <a href="{{ route('cation_exchange_analysis.index') }}" class="nav-link text-success">
                                    <i class="nav-icon fas fa-flask"></i>
                                    <p>Intercambio Catiónico</p>
                                </a>
                            </li>
                        @endif

                        @if (Auth::user()->role === 'personal_tecnico')
                            <li class="nav-item">
                                <a href="{{ route('phosphorus_analysis.index') }}" class="nav-link text-success">
                                    <i class="nav-icon fas fa-flask"></i>
                                    <p>Fósforo</p>
                                </a>
                            </li>
                        @endif

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('contenido')
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Include additional scripts from child views -->
    @stack('scripts')
</body>
</html>