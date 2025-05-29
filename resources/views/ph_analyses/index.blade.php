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

            <!-- Pending Analyses Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Procesar Análisis de pH</h3>
                </div>
                <div class="card-body">
                    @if ($processes->isEmpty())
                        <p>No hay análisis de pH pendientes para procesar.</p>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Proceso</th>
                                    <th>Cantidad de Análisis Pendientes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($processes as $process)
                                    <tr>
                                        <td>{{ $process->process_id }}</td>
                                        <td>{{ $process->analyses->where('status', 'pending')->count() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <a href="{{ route('ph_analysis.process_all') }}" class="btn btn-primary">
                                Iniciar Proceso
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Batch pH Analysis Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Análisis de pH Pendientes (Procesar en Lotes)</h3>
                </div>
                <div class="card-body">
                    @include('partials.pending_ph_batch')
                </div>
            </div>
        </div>
    </section>
</div>
@endsection