@extends('layouts.app')

@section('title', 'Gestión de Análisis de pH')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de pH</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Gestión de pH</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Análisis de pH</h3>
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

                    @if ($phAnalyses->isEmpty())
                        <p>No hay análisis de pH registrados.</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Proceso</th>
                                    <th>Servicio</th>
                                    <th>Fecha de Análisis</th>
                                    <th>Estado de Revisión</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($phAnalyses as $phAnalysis)
                                    <tr>
                                        <td>{{ $phAnalysis->analysis->process->process_id }}</td>
                                        <td>{{ $phAnalysis->analysis->service->descripcion }}</td>
                                        <td>{{ $phAnalysis->fecha_analisis }}</td>
                                        <td>
                                            @if ($phAnalysis->review_status === 'pending')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @elseif ($phAnalysis->review_status === 'approved')
                                                <span class="badge badge-success">Aprobado</span>
                                            @elseif ($phAnalysis->review_status === 'rejected')
                                                <span class="badge badge-danger">Rechazado</span>
                                            @else
                                                <span class="badge badge-secondary">Sin revisar</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($phAnalysis->review_status === 'rejected')
                                                <a href="{{ route('ph_analysis.edit_analysis', $phAnalysis->id) }}" class="btn btn-warning btn-sm">
                                                    Corregir
                                                </a>
                                            @endif
                                            <a href="{{ route('ph_analysis.download_report', $phAnalysis->analysis->id) }}" class="btn btn-info btn-sm">
                                                Descargar Reporte
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $phAnalyses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection