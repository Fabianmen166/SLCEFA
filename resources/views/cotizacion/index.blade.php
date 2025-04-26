@extends('layouts.master')

@section('contenido')
    <center>
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%">
    </center>

    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <br><br>

        <center>
            <div class="container">
                <div class="card">
                    <h5 class="card-header">Lista de Cotizaciones</h5>
                    <div class="card-body">
                        <form method="GET" action="{{ route('cotizacion.index') }}" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" name="nit" placeholder="Buscar por NIT o ID de cotización" value="{{ request('nit') }}">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </form>

                        <a href="{{ route('cotizacion.create') }}" class="btn btn-success mb-3">Crear Nueva Cotización</a>

                        @if ($quotes->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID Cotización</th>
                                            <th>Cliente (NIT)</th>
                                            <th>Tipo de Solicitante</th>
                                            <th>Servicios y Paquetes</th>
                                            <th>Total</th>
                                            <th>Creado por</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotes as $quote)
                                            <tr>
                                                <td>{{ $quote->quote_id ?? 'N/A' }}</td>
                                                <td>{{ $quote->customer->nit ?? 'N/A' }}</td>
                                                <td>{{ $quote->customer->solicitante ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($quote->quoteServices->isNotEmpty())
                                                        <ul>
                                                            @foreach ($quote->quoteServices as $quoteService)
                                                                @if ($quoteService->services_id && $quoteService->service)
                                                                    <li>
                                                                        {{ $quoteService->service->descripcion ?? 'Servicio no encontrado' }} (Cantidad: {{ $quoteService->cantidad }})
                                                                    </li>
                                                                @elseif ($quoteService->service_packages_id && $quoteService->servicePackage)
                                                                    <li>
                                                                        {{ $quoteService->servicePackage->nombre ?? 'Paquete no encontrado' }} (Cantidad: {{ $quoteService->cantidad }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        Sin servicios ni paquetes
                                                    @endif
                                                </td>
                                                <td>{{ number_format($quote->total, 2) }}</td>
                                                <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('cotizacion.show', $quote->quote_id) }}" class="btn btn-info btn-sm">Ver</a>
                                                    <a href="{{ route('cotizacion.edit', $quote->quote_id) }}" class="btn btn-warning btn-sm">Editar</a>
                                                    <form action="{{ route('cotizacion.destroy', $quote->quote_id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta cotización?')">Eliminar</button>
                                                    </form>
                                                    <a href="{{ route('cotizacion.comprobante', $quote->quote_id) }}" class="btn btn-secondary btn-sm">Descargar PDF</a>
                                                    <a href="{{ route('cotizacion.show_upload_form', $quote->quote_id) }}" class="btn btn-primary btn-sm">Subir Comprobante</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No se encontraron cotizaciones.</p>
                        @endif

                        <a href="{{ route('gestion_calidad.dashboard') }}" class="back-btn">Volver al Dashboard</a>
                    </div>
                </div>
            </div>
        </center>
    </div>
@endsection