<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('images/Favicon2.png') }}" type="image/x-icon">
    <title>Bienvenido Gestión de Calidad</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    {{ Auth::user()->name }}
                </a>

                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-success-green elevation-4">
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <br><br><br><br>

                    <!-- Cotizaciones -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fa-solid fa-money-check-dollar"></i> 
                            <p>Cotizaciones</p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('cotizacion.create') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>Ingreso</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cotizacion.lista') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Listas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Usuarios -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fa-solid fa-user-plus"></i> 
                            <p>Usuarios</p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('customers.create') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>Ingreso</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('listac') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Listas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Servicios -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fa-solid fa-cogs"></i> 
                            <p>Servicios</p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('services.create') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>Ingreso</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('listas') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Listas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Paquetes de Servicios -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-success">
                            <i class="fa-solid fa-boxes-stacked"></i> 
                            <p>Paquetes de Servicios</p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('service_packages.create') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-edit"></i>
                                    <p>Ingreso</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('service_packages.index') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Listas</p>
                                </a>
                            </li>
                        </ul>
                    </li>
<!-- Roles Mayordomo (Tipos de Cliente) -->
<li class="nav-item has-treeview">
    <a href="#" class="nav-link text-success">
        <i class="fa-solid fa-user-tag"></i> 
        <p>Tipos de Cliente</p>
        <i class="fas fa-angle-left right"></i>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('customer_types.create') }}" class="nav-link text-dark">
                <i class="nav-icon fas fa-edit"></i>
                <p>Ingreso</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('customer_types.index') }}" class="nav-link text-dark">
                <i class="nav-icon fas fa-clipboard-list"></i>
                <p>Listas</p>
            </a>
        </li>
    </ul>
</li>

                    <!-- Análisis de Producción -->
                    <li class="nav-item">
                        <a href="{{ asset('AdminLTE-3.2.0/pages/widgets.html') }}" class="nav-link text-success">
                            <i class="fas fa-chart-bar"></i> 
                            <p>Análisis de Producción</p>
                        </a>
                    </li>

                    <!-- Geo Referencias -->
                    <li class="nav-item">
                        <a href="#" class="nav-link text-success">
                            <i class="nav-icon fas fa-globe"></i>
                            <p>Geo Referencias</p>
                        </a>
                    </li>

                    <!-- Reportes -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link text-danger">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Reportes<i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ asset('AdminLTE-3.2.0/pages/tables/simple.html') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Actividades</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ asset('AdminLTE-3.2.0/pages/tables/data.html') }}" class="nav-link text-dark">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Contable</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <div class="content-wrapper">
        @yield('contenido')
    </div>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('AdminLTE-3.2.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('AdminLTE-3.2.0/dist/js/adminlte.js') }}"></script>
    <!-- Include additional scripts from child views -->
    @yield('scripts')
</body>

</html>