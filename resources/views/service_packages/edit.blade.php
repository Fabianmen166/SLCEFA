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
                            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $servicePackage->nombre) }}" required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio</label>
                            <input type="number" name="precio" id="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $servicePackage->precio) }}" step="0.01" required>
                            @error('precio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="acreditado" id="acreditado" value="1" {{ old('acreditado', $servicePackage->acreditado) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="acreditado">Acreditado</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Servicios Incluidos</label>
                            @error('included_services')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            
                            @php
                                // Decodificar los servicios incluidos del paquete
                                $selectedServices = json_decode($servicePackage->getRawOriginal('included_services'), true) ?? [];
                                // Usar los valores antiguos (old) si existen (para cuando hay errores de validación)
                                $selectedServices = old('included_services', $selectedServices);
                            @endphp
                            
                            <div class="row">
                                @foreach($services as $service)
                                    <div class="col-md-4">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                   name="included_services[]" 
                                                   id="service_{{ $service->services_id }}" 
                                                   value="{{ $service->services_id }}"
                                                   {{ is_array($selectedServices) && in_array($service->services_id, $selectedServices) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="service_{{ $service->services_id }}">
                                                {{ $service->descripcion }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Paquete
                            </button>
                            <a href="{{ route('service_packages.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .custom-checkbox {
            margin-bottom: 8px;
        }
    </style>
@endpush