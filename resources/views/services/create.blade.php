@extends('layouts.master')

@section('contenido')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Crear Servicio</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('listas') }}">Servicios</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Creación</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('services.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ old('descripcion') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="precio">Precio:</label>
                            <input type="number" name="precio" id="precio" class="form-control" step="0.01" value="{{ old('precio') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="acreditado">Acreditado:</label>
                            <input type="checkbox" name="acreditado" id="acreditado" value="1" {{ old('acreditado') ? 'checked' : '' }}>
                        </div>
                        <button type="submit" class="btn btn-primary">Crear</button>
                        <a href="{{ route('listas') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection