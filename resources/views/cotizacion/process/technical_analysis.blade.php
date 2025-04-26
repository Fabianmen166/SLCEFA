<!-- resources/views/processes/technical_analysis.blade.php -->
@extends('layouts.master')

@section('title', 'Realizar Análisis Técnico')

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Realizar Análisis Técnico - {{ $targetService ?? 'Todos' }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('process.technical_index') }}">Análisis Técnico</a></li>
                    <li class="breadcrumb-item active">Realizar Análisis</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Procesos y Servicios</h3>
            </div>
            <div class="card-body">
                @if($analysisForms->isEmpty())
                    <p>No hay análisis pendientes para este proceso.</p>
                @else
                    <form action="{{ route('process.store_analysis', ['process_id' => $process->process_id, 'service_id' => $analysisForms->first()['service']->services_id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_type" value="{{ $analysisForms->first()['form_type'] }}">
                        <div class="form-group">
                            <label>Fecha de Análisis</label>
                            <input type="date" name="analysis_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nombre del Analista</label>
                            <input type="text" name="analyst_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Muestras (Máximo 20)</label>
                            @for($i = 0; $i < 20; $i++)
                                <div class="card mt-2">
                                    <div class="card-header">
                                        Muestra {{ $i + 1 }}
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <input type="text" name="samples[{{ $i }}][internal_code]" class="form-control" placeholder="Código Interno" required>
                                        </div>
                                        @if($analysisForms->first()['form_type'] === 'ph')
                                            <div class="form-group">
                                                <input type="number" step="0.01" name="samples[{{ $i }}][weight]" class="form-control" placeholder="Peso (g)" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="number" step="0.01" name="samples[{{ $i }}][water_volume]" class="form-control" placeholder="Volumen de Agua (ml)" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="number" step="0.01" name="samples[{{ $i }}][temperature]" class="form-control" placeholder="Temperatura (°C)" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="number" step="0.01" name="samples[{{ $i }}][ph_value]" class="form-control" placeholder="pH" required min="0" max="14">
                                            </div>
                                        @endif
                                        <div class="form-group">
                                            <textarea name="samples[{{ $i }}][observations]" class="form-control" placeholder="Observaciones"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection