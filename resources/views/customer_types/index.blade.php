@extends('layouts.master')

@section('contenido')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tipos de Cliente</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Tipos de Cliente</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="cardVcard">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Tipos de Cliente</h3>
                        <div class="card-tools">
                            <a href="{{ route('customer_types.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Crear Nuevo Tipo de Cliente
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($customerTypes && $customerTypes->count() > 0)
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Porcentaje de Descuento</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customerTypes as $customerType)
                                        <tr>
                                            <td>{{ $customerType->name }}</td>
                                            <td>{{ $customerType->discount_percentage }}%</td>
                                            <td>{{ $customerType->description }}</td>
                                            <td>
                                                <a href="{{ route('customer_types.edit', $customerType->customer_type_id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <form action="{{ route('customer_types.destroy', $customerType->customer_type_id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este tipo de cliente?')">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No hay tipos de clientes disponibles.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    @endsection