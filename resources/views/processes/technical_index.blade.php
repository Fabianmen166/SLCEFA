@extends('layouts.app')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesos Técnicos Pendientes</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Procesos Técnicos</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- General Processes Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Procesos Pendientes</h3>
                </div>
                <div class="card-body">
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

                    @if ($processes->isEmpty())
                        <p>No hay procesos pendientes.</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Código de Ensayo</th>
                                    <th>Servicios Pendientes</th>
                                    <th>Servicios Realizados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($processes as $process)
                                    @php
                                        $pendingAnalyses = $process->analyses->where('status', 'pending');
                                        $completedAnalyses = $process->analyses->where('status', 'completed');
                                        $rowspan = max($pendingAnalyses->count(), 1);
                                    @endphp
                                    @if($pendingAnalyses->isEmpty())
                                    <tr>
                                        <td>{{ $process->process_id }}</td>
                                            <td><span class="text-gray-500">Ningún servicio pendiente</span></td>
                                            <td>
                                                @if ($completedAnalyses->isEmpty())
                                                    <span class="text-gray-500">Ningún servicio realizado</span>
                                            @else
                                                <ul class="list-unstyled">
                                                        @foreach ($completedAnalyses as $analysis)
                                                            <li>{{ $analysis->service->descripcion }} (Cantidad: {{ $analysis->cantidad ?? 'No especificada' }})</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        </tr>
                                    @else
                                        @foreach ($pendingAnalyses as $i => $analysis)
                                            <tr>
                                                @if($i === 0)
                                                    <td rowspan="{{ $rowspan }}">{{ $process->process_id }}</td>
                                                @endif
                                                <td>{{ $analysis->service->descripcion }} (Cantidad: {{ $analysis->cantidad ?? 'No especificada' }})</td>
                                                @if($i === 0)
                                                    <td rowspan="{{ $rowspan }}">
                                            @if ($completedAnalyses->isEmpty())
                                                <span class="text-gray-500">Ningún servicio realizado</span>
                                            @else
                                                <ul class="list-unstyled">
                                                                @foreach ($completedAnalyses as $cAnalysis)
                                                                    <li>{{ $cAnalysis->service->descripcion }} (Cantidad: {{ $cAnalysis->cantidad ?? 'No especificada' }})</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                                @endif
                                    </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- Returned Analyses Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Análisis de pH Devueltos</h3>
                </div>
                <div class="card-body">
                    @php
                        $returnedPhAnalyses = collect();
                        foreach ($processes as $process) {
                            foreach ($process->analyses as $analysis) {
                                if (
                                    ($analysis->phAnalysis && ($analysis->phAnalysis->review_status === 'rejected' || $analysis->approved === 0))
                                ) {
                                    $returnedPhAnalyses->push($analysis);
                                }
                            }
                        }
                    @endphp
                    @if ($returnedPhAnalyses->isEmpty())
                        <p>No hay análisis devueltos para mostrar.</p>
                    @else
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Proceso</th>
                                        <th>Servicio</th>
                                    <th>Fecha de Creación</th>
                                    <th>Estado de Revisión</th>
                                    <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($returnedPhAnalyses as $analysis)
                                    <tr>
                                        <td>{{ $analysis->process ? $analysis->process->process_id : 'No asignado' }}</td>
                                        <td>{{ $analysis->service ? $analysis->service->descripcion : 'Sin servicio' }}</td>
                                        <td>{{ $analysis->created_at }}</td>
                                        <td>
                                            <span class="badge badge-danger">Rechazado</span>
                                        </td>
                                        <td>
                                            @if ($analysis->process)
                                                <a href="{{ route('ph_analysis.edit_analysis', $analysis->id) }}" class="btn btn-warning btn-sm">
                                                    Corregir
                                                </a>
                                                <a href="{{ route('ph_analysis.download_report', $analysis->id) }}" class="btn btn-info btn-sm">
                                                    Descargar Reporte
                                                </a>
                                            @else
                                                <span class="text-muted">Acciones no disponibles</span>
                                            @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    @endif
                </div>
            </div>

            <!-- Batch pH Analysis Section (Unchanged) -->
            {{-- Sección eliminada: Análisis de pH Pendientes (Procesar en Lotes) --}}
        </div>
    </section>
</div>
@endsection