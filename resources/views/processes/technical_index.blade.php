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
                                    <tr>
                                        <td>{{ $process->process_id }}</td>
                                        <td>
                                            @php
                                                $pendingAnalyses = $process->analyses->where('status', 'pending');
                                            @endphp
                                            @if ($pendingAnalyses->isEmpty())
                                                <span class="text-gray-500">Ningún servicio pendiente</span>
                                            @else
                                                <ul class="list-unstyled">
                                                    @foreach ($pendingAnalyses as $analysis)
                                                        <li>
                                                            {{ $analysis->service->descripcion }} (Cantidad: {{ $analysis->cantidad ?? 'No especificada' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $completedAnalyses = $process->analyses->where('status', 'completed');
                                            @endphp
                                            @if ($completedAnalyses->isEmpty())
                                                <span class="text-gray-500">Ningún servicio realizado</span>
                                            @else
                                                <ul class="list-unstyled">
                                                    @foreach ($completedAnalyses as $analysis)
                                                        <li>
                                                            {{ $analysis->service->descripcion }} (Cantidad: {{ $analysis->cantidad ?? 'No especificada' }})
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

            <!-- Batch pH Analysis Section (Unchanged) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis de pH Pendientes (Procesar en Lotes)</h3>
                </div>
                <div class="card-body">
                    @php
                        $pendingPhAnalyses = [];
                        foreach ($processes as $process) {
                            $phAnalyses = $process->pendingPhAnalyses()->get();
                            foreach ($phAnalyses as $analysis) {
                                $pendingPhAnalyses[] = [
                                    'process_id' => $process->process_id,
                                    'service_id' => $analysis->service_id,
                                    'analysis_id' => $analysis->id,
                                    'descripcion' => $analysis->service->descripcion,
                                    'cantidad' => $analysis->cantidad,
                                ];
                            }
                        }
                    @endphp

                    @if (empty($pendingPhAnalyses))
                        <p>No hay análisis de pH pendientes.</p>
                    @else
                        <form action="{{ route('ph_analysis.batch_ph_analysis') }}" method="POST">
                            @csrf
                            <div class="alert alert-info">
                                <p><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de pH para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
                            </div>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Seleccionar</th>
                                        <th>Proceso</th>
                                        <th>Servicio</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingPhAnalyses as $index => $phAnalysis)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="analyses[]" value="{{ $phAnalysis['analysis_id'] }}" class="ph-checkbox">
                                            </td>
                                            <td>{{ $phAnalysis['process_id'] }}</td>
                                            <td>{{ $phAnalysis['descripcion'] }}</td>
                                            <td>{{ $phAnalysis['cantidad'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary" id="process-batch-btn" disabled>
                                Procesar Lote de Análisis de pH
                            </button>
                        </form>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const checkboxes = document.querySelectorAll('.ph-checkbox');
                                const submitButton = document.getElementById('process-batch-btn');

                                function updateButtonState() {
                                    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                                    if (checkedCount > 0 && checkedCount <= 20) {
                                        submitButton.disabled = false;
                                    } else {
                                        submitButton.disabled = true;
                                    }

                                    if (checkedCount > 20) {
                                        alert('No puede seleccionar más de 20 análisis de pH a la vez.');
                                        Array.from(checkboxes).filter(cb => cb.checked).slice(20).forEach(cb => cb.checked = false);
                                    }
                                }

                                checkboxes.forEach(cb => cb.addEventListener('change', updateButtonState));
                            });
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection