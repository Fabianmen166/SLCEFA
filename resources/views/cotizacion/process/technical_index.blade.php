<!-- resources/views/processes/technical_index.blade.php -->
@extends('layouts.master')

@section('title', 'Análisis Técnico')

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Análisis Técnico</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Análisis Técnico</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Procesos Pendientes</h3>
            </div>
            <div class="card-body">
                @if($processes->isEmpty())
                    <p>No hay procesos pendientes asignados.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Proceso</th>
                                <th>Cotización</th>
                                <th>Servicios Pendientes</th>
                                <th>Servicios Realizados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processes as $process)
                                <tr>
                                    <td>{{ $process->process_id }}</td>
                                    <td>{{ $process->quote->quote_id }}</td>
                                    <td>
                                        <ul>
                                            @foreach($process->services->where('pivot.status', 'pending') as $service)
                                                <li>{{ $service->descripcion }} ({{ $service->pivot->cantidad }} pendientes)</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach($process->serviceDetails->where('status', 'completed') as $detail)
                                                <li>{{ $detail->service->descripcion }} (Resultado: {{ $detail->result ?? 'Sin resultado' }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        @foreach($process->services->where('pivot.status', 'pending') as $service)
                                            @if(in_array($service->descripcion, ['pH en Suelos', 'Acidez Intercambiable en Suelos', 'Conductividad Eléctrica en Suelos']))
                                                <a href="{{ route('process.technical_analysis', ['process_id' => $process->process_id, 'service_type' => strtolower(str_replace(' ', '_', $service->descripcion))) }}"
                                                   class="btn btn-sm btn-primary mb-1">
                                                    Realizar {{ $service->descripcion }}
                                                </a>
                                            @endif
                                        @endforeach
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