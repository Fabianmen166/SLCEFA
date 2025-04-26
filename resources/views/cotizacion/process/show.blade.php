<!-- resources/views/cotizacion/process/show.blade.php -->
@extends('layouts.master')

@section('title', 'Detalles del Proceso')

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detalles del Proceso</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cotizacion.process.index') }}">Procesos Abiertos</a></li>
                    <li class="breadcrumb-item active">Detalles</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Información del Proceso -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información del Proceso</h3>
            </div>
            <div class="card-body">
                <p><strong>ID del Proceso:</strong> {{ $process->id }}</p>
                <p><strong>Estado:</strong> {{ $process->status }}</p>
                <p><strong>Fecha de Inicio:</strong> {{ $process->created_at->format('d/m/Y') }}</p>
                <p><strong>Item Code:</strong> {{ $process->item_code }}</p>
                <p><strong>Comunicación con el Cliente:</strong> {{ $process->comunicacion_cliente ?? 'No especificado' }}</p>
                <p><strong>Días para Procesar:</strong> {{ $process->dias_procesar }}</p>
                <p><strong>Fecha de Recepción:</strong> {{ $process->fecha_recepcion->format('d/m/Y') }}</p>
                <p><strong>Descripción:</strong> {{ $process->descripcion ?? 'No especificada' }}</p>
                <p><strong>Lugar de Muestreo:</strong> {{ $process->lugar_muestreo ?? 'No especificado' }}</p>
                <p><strong>Fecha de Muestreo:</strong> {{ $process->fecha_muestreo ? \Carbon\Carbon::parse($process->fecha_muestreo)->format('d/m/Y') : 'No especificada' }}</p>
                <p><strong>Responsable de Recepción:</strong> {{ $process->responsable_recepcion }}</p>
                <p><strong>Fecha de Entrega:</strong> {{ $process->fecha_entrega->format('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Información de la Cotización -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información de la Cotización</h3>
            </div>
            <div class="card-body">
                <p><strong>ID de la Cotización:</strong> {{ $process->quote->quote_id }}</p>
                <p><strong>Total:</strong> {{ number_format($process->quote->total, 2) }}</p>
                <p><strong>Fecha de Creación:</strong> {{ $process->quote->created_at->format('d/m/Y') }}</p>
                <h5>Datos del Cliente:</h5>
                <p><strong>Solicitante:</strong> {{ $process->quote->customer->solicitante ?? 'No especificado' }}</p>
                <p><strong>Contacto:</strong> {{ $process->quote->customer->contacto }}</p>
                <p><strong>Teléfono:</strong> {{ $process->quote->customer->telefono ?? 'No especificado' }}</p>
                <p><strong>NIT:</strong> {{ $process->quote->customer->nit }}</p>
                <p><strong>Correo:</strong> {{ $process->quote->customer->correo ?? 'No especificado' }}</p>
                <p><strong>Tipo de Cliente:</strong> {{ $process->quote->customer->customerType->name ?? 'No especificado' }}</p>
            </div>
        </div>

        <!-- Ítems de la Cotización (Servicios y Paquetes) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ítems de la Cotización</h3>
            </div>
            <div class="card-body">
                @if($quoteItems->isEmpty())
                    <p>No hay ítems asociados a esta cotización. Asegúrate de que la cotización tenga servicios o paquetes asignados.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Acreditado</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quoteItems as $item)
                                <tr>
                                    <td>{{ $item->type === 'service' ? 'Servicio' : 'Paquete de Servicios' }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->accredited ? 'Sí' : 'No' }}</td>
                                    <td>{{ $item->quantity ?? '-' }}</td>
                                    <td>{{ $item->subtotal ? number_format($item->subtotal, 2) : '-' }}</td>
                                    <td>
                                        @if($item->type === 'package')
                                            <p><strong>Servicios en el Paquete:</strong></p>
                                            <ul>
                                                @foreach($item->services as $service)
                                                    <li>
                                                        {{ $service['name'] }} (Precio: {{ number_format($service['price'], 2) }})
                                                        - Acreditado: {{ $service['accredited'] ? 'Sí' : 'No' }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Servicios a Realizar -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Servicios a Realizar</h3>
            </div>
            <div class="card-body">
                @if($servicesToDo->isEmpty())
                    <p>No hay servicios asociados a este proceso.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Paquete (si aplica)</th>
                                <th>Precio</th>
                                <th>Acreditado</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicesToDo as $service)
                                <tr>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->package_name ?? '-' }}</td>
                                    <td>{{ number_format($service->price, 2) }}</td>
                                    <td>{{ $service->accredited ? 'Sí' : 'No' }}</td>
                                    <td>{{ $service->quantity ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Servicios Pendientes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Servicios Pendientes</h3>
            </div>
            <div class="card-body">
                @if($pendingServices->isEmpty())
                    <p>No hay servicios pendientes.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Paquete (si aplica)</th>
                                <th>Precio</th>
                                <th>Acreditado</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingServices as $service)
                                <tr>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->package_name ?? '-' }}</td>
                                    <td>{{ number_format($service->price, 2) }}</td>
                                    <td>{{ $service->accredited ? 'Sí' : 'No' }}</td>
                                    <td>{{ $service->quantity ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Servicios Realizados -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Servicios Realizados</h3>
            </div>
            <div class="card-body">
                @if($completedServices->isEmpty())
                    <p>No hay servicios realizados.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Paquete (si aplica)</th>
                                <th>Precio</th>
                                <th>Acreditado</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedServices as $service)
                                <tr>
                                    <td>{{ $service->name }}</td>
                                    <td>{{ $service->package_name ?? '-' }}</td>
                                    <td>{{ number_format($service->price, 2) }}</td>
                                    <td>{{ $service->accredited ? 'Sí' : 'No' }}</td>
                                    <td>{{ $service->quantity ?? '-' }}</td>
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