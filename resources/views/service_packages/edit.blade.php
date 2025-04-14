@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Paquete de Servicios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('service_packages.index') }}">Paquetes de Servicios</a></li>
                        <li class="breadcrumb-item active">Editar Paquete de Servicios</li>
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
                    <h3 class="card-title">Formulario de Edición de Paquete de Servicios</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('service_packages.update', $servicePackage->service_packages_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $servicePackage->nombre) }}">
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" name="precio" id="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $servicePackage->precio) }}" step="0.01">
                            @error('precio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="acreditado">Acreditado</label>
                            <input type="checkbox" name="acreditado" id="acreditado" value="1" {{ old('acreditado', $servicePackage->acreditado) ? 'checked' : '' }}>
                        </div>

                        <div class="form-group">
                            <label for="included_services">Servicios Incluidos</label>
                            <select name="included_services[]" id="included_services" class="form-control @error('included_services') is-invalid @enderror" multiple>
                                @foreach($services as $service)
                                    <option value="{{ $service->services_id }}" 
                                        {{ in_array($service->services_id, old('included_services', json_decode($servicePackage->getRawOriginal('included_services'), true) ?? [])) ? 'selected' : '' }}>
                                        {{ $service->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('included_services')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar Paquete de Servicios</button>
                        <a href="{{ route('service_packages.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection