@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Crear Paquete de Servicios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('service_packages.index') }}">Paquetes de Servicios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                    <h3 class="card-title">Formulario de Creación</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('service_packages.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="precio">Precio:</label>
                            <input type="number" name="precio" id="precio" class="form-control" step="0.01" value="{{ old('precio') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="acreditado">Acreditado:</label>
                            <input type="checkbox" name="acreditado" id="acreditado" value="1" {{ old('acreditado') ? 'checked' : '' }}>
                        </div>
                        <div class="form-group">
                            <h3>Servicios Incluidos:</h3>
                            @foreach($services as $service)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="services[]" id="service_{{ $service->services_id }}" value="{{ $service->services_id }}"
                                        {{ in_array($service->services_id, old('services', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="service_{{ $service->services_id }}">
                                        {{ $service->descripcion }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary">Crear</button>
                        <a href="{{ route('service_packages.index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection