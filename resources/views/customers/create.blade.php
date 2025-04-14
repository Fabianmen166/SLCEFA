@extends('layouts.master')

@section('contenido')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Crear Cliente</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('listac') }}">Clientes</a></li>
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
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="solicitante">Solicitante:</label>
                            <input type="text" name="solicitante" id="solicitante" class="form-control" value="{{ old('solicitante') }}">
                        </div>
                        <div class="form-group">
                            <label for="contacto">Contacto:</label>
                            <input type="text" name="contacto" id="contacto" class="form-control" value="{{ old('contacto') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="nit">NIT:</label>
                            <input type="text" name="nit" id="nit" class="form-control" value="{{ old('nit') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input type="email" name="correo" id="correo" class="form-control" value="{{ old('correo') }}">
                        </div>
                        <div class="form-group">
                            <label for="customer_type_id">Tipo de Cliente:</label>
                            <select name="customer_type_id" id="customer_type_id" class="form-control" required>
                                <option value="">Seleccione un tipo de cliente</option>
                                @foreach($customerTypes as $customerType)
                                    <option value="{{ $customerType->customer_type_id }}" {{ old('customer_type_id') == $customerType->customer_type_id ? 'selected' : '' }}>
                                        {{ $customerType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Crear</button>
                        <a href="{{ route('listac') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection