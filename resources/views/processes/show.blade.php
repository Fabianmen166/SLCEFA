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
                    <li class="breadcrumb-item"><a href="{{ route('processes.index') }}">Procesos Abiertos</a></li>
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
                <p><strong>ID del Proceso:</strong> {{ $process->process_id }}</p>
                <p><strong>Estado:</strong> {{ $process->status }}</p>
                <p><strong>Fecha de Inicio:</strong> {{ $process->created_at->format('d/m/Y') }}</p>
                <p><strong>Item Code:</strong> {{ $process->item_code }}</p>
                <p><strong>Comunicación con el Cliente:</strong> {{ $process->comunicacion_cliente ?? 'No especificado' }}</p>
                <p><strong>Días para Procesar:</strong> {{ $process->dias_procesar }}</p>
                <p><strong>Fecha de Recepción:</strong> {{ $process->fecha_recepcion->format('d/m/Y') }}</p>
                <p><strong>Descripción:</strong> {{ $process->descripcion ?? 'No especificada' }}</p>
                <p><strong>Lugar de Muestreo:</strong> {{ $process->lugar_muestreo ?? 'No especificado' }}</p>
                <p><strong>Fecha de Muestreo:</strong> {{ $process->fecha_muestreo ? \Carbon\Carbon::parse($process->fecha_muestreo)->format('d/m/Y') : 'No especificada' }}</p>
                <p><strong>Responsable de Recepción:</strong> {{ $process->responsable->name ?? 'No especificado' }}</p>
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

        <!-- Servicios a Realizar -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Servicios a Realizar</h3>
            </div>
            <div class="card-body">
                @php
                    $processServices = $process->serviceProcessDetails ?? collect();
                @endphp
                @if($processServices->isEmpty())
                    <p>No hay servicios asociados a este proceso.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processServices as $ps)
                                @if($ps->quoteService && $ps->quoteService->services_id && $ps->quoteService->service)
                                    <tr>
                                        <td>Servicio</td>
                                        <td>{{ $ps->quoteService->service->descripcion ?? 'Servicio no encontrado' }}</td>
                                        <td>{{ $ps->quoteService->cantidad }}</td>
                                </tr>
                                @elseif($ps->quoteService && $ps->quoteService->service_packages_id && $ps->quoteService->servicePackage)
                                    <tr>
                                        <td>Paquete</td>
                                        <td>{{ $ps->quoteService->servicePackage->nombre ?? 'Paquete no encontrado' }}</td>
                                        <td>{{ $ps->quoteService->cantidad }}</td>
                                </tr>
                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection