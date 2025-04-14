@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Paquetes de Servicios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Paquetes de Servicios</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            @if (isset($error))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ $error }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Paquetes de Servicios</h3>
                    <div class="card-tools">
                        <a href="{{ route('service_packages.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Crear Nuevo Paquete de Servicios
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($servicePackages) && $servicePackages->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Acreditado</th>
                                    <th>Servicios Incluidos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servicePackages as $servicePackage)
                                    <tr>
                                        <td>{{ $servicePackage->nombre }}</td>
                                        <td>{{ $servicePackage->precio }}</td>
                                        <td>{{ $servicePackage->acreditado ? 'Sí' : 'No' }}</td>
                                        <td>
                                            @if($servicePackage->services && $servicePackage->services->count() > 0)
                                                @foreach($servicePackage->services as $service)
                                                    {{ $service->descripcion }}
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            @else
                                                Ninguno
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('service_packages.edit', $servicePackage->service_packages_id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <form action="{{ route('service_packages.destroy', $servicePackage->service_packages_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este paquete de servicios?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No hay paquetes de servicios disponibles.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection