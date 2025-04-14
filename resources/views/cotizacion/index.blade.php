@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Lista de Cotizaciones</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Cotizaciones</li>
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

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cotizaciones</h3>
                    <div class="card-tools">
                        <a href="{{ route('cotizacion.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Crear Nueva Cotización
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($quotes->isEmpty())
                        <p>No hay cotizaciones disponibles.</p>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Cotización</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotes as $quote)
                                    <tr>
                                        <td>{{ $quote->quote_id }}</td>
                                        <td>{{ $quote->customer->contacto ?? 'Sin cliente' }}</td>
                                        <td>{{ number_format($quote->total, 2) }}</td>
                                        <td>{{ $quote->user->name ?? 'Sin usuario' }}</td>
                                        <td>
                                            <a href="{{ route('cotizacion.edit', $quote->quote_id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="{{ route('cotizacion.upload', $quote->quote_id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-upload"></i> Subir Comprobante
                                            </a>
                                            <a href="{{ route('cotizacion.comprobante', $quote->quote_id) }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-file-pdf"></i> Generar PDF
                                            </a>
                                            <form action="{{ route('cotizacion.destroy', $quote->quote_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta cotización?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection