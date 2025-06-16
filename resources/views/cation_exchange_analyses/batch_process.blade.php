@extends('layouts.app')

@section('title', 'Procesar Análisis de Intercambio Catiónico')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Intercambio Catiónico</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cation_exchange_analysis.index') }}">Gestión de Intercambio Catiónico</a></li>
                        <li class="breadcrumb-item active">Procesar Análisis</li>
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

            @if ($pendingAnalyses->isEmpty())
                <div class="alert alert-danger">
                    Error: No hay análisis de Intercambio Catiónico pendientes para procesar.
                </div>
                <a href="{{ route('cation_exchange_analysis.index') }}" class="btn btn-secondary">Regresar</a>
            @else
                <!-- Usamos el primer análisis para las secciones generales -->
                @php
                    $firstAnalysis = $pendingAnalyses->first();
                @endphp

                <form action="{{ route('cation_exchange_analysis.store_batch_process') }}" method="POST" id="cationExchangeAnalysisForm">
                    @csrf

                    <!-- Hidden Input for Analysis IDs -->
                    @foreach ($pendingAnalyses as $analysis)
                        <input type="hidden" name="analysis_ids[]" value="{{$analysis->id}}">
                    @endforeach

                    <!-- Sección: Información General -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información General</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="process_id">Procesos Involucrados</label>
                                        <input type="text" class="form-control" id="process_id" value="{{ $pendingAnalyses->pluck('process.process_id')->unique()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="service">Servicios Involucrados</label>
                                        <input type="text" class="form-control" id="service" value="{{ $pendingAnalyses->pluck('service.descripcion')->unique()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="consecutivo_no">Consecutivo No.</label>
                                        <input type="text" class="form-control @error('consecutivo_no') is-invalid @enderror" id="consecutivo_no" name="consecutivo_no" value="{{ old('consecutivo_no', '') }}" required>
                                        @error('consecutivo_no')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fecha_analisis">Fecha del Análisis</label>
                                        <input type="date" class="form-control @error('fecha_analisis') is-invalid @enderror" id="fecha_analisis" name="fecha_analisis" value="{{ old('fecha_analisis', now()->format('Y-m-d')) }}" required>
                                        @error('fecha_analisis')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="analista">Analista</label>
                                        <input type="text" class="form-control" id="analista" value="{{ Auth::user()->name }}" readonly>
                                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="normalidad_naoh">Normalidad de NaOH (N)</label>
                                        <input type="number" step="0.0001" class="form-control @error('normalidad_naoh') is-invalid @enderror" id="normalidad_naoh" name="normalidad_naoh" value="{{ old('normalidad_naoh', '') }}" required>
                                        @error('normalidad_naoh')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Controles Analíticos -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Controles Analíticos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Blanco del Proceso -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="blanco_valor_leido">Blanco del Proceso (mg/L)</label>
                                        <input type="number" step="0.01" class="form-control @error('blanco_valor_leido') is-invalid @enderror" id="blanco_valor_leido" name="blanco_valor_leido" value="{{ old('blanco_valor_leido', $firstAnalysis->cationExchangeAnalysis->controles_analiticos['blanco_valor_leido'] ?? '') }}" required>
                                        @error('blanco_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="blanco_observaciones">Observaciones del Blanco</label>
                                        <input type="text" class="form-control" id="blanco_observaciones" name="blanco_observaciones" value="{{ old('blanco_observaciones', $firstAnalysis->cationExchangeAnalysis->controles_analiticos['blanco_observaciones'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="blanco_estado">Estado</label>
                                        <input type="text" class="form-control" id="blanco_estado" name="blanco_estado" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Veracidad Analítica -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="veracidad_valor_esperado">Veracidad Analítica - Valor Esperado (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('veracidad_valor_esperado') is-invalid @enderror" id="veracidad_valor_esperado" name="veracidad_valor_esperado" value="{{ old('veracidad_valor_esperado', $firstAnalysis->cationExchangeAnalysis->veracidad_analitica['valor_esperado'] ?? '') }}" required>
                                        @error('veracidad_valor_esperado')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="veracidad_valor_leido">Veracidad Analítica - Valor Leído (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('veracidad_valor_leido') is-invalid @enderror" id="veracidad_valor_leido" name="veracidad_valor_leido" value="{{ old('veracidad_valor_leido', $firstAnalysis->cationExchangeAnalysis->veracidad_analitica['valor_leido'] ?? '') }}" required>
                                        @error('veracidad_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="veracidad_recuperacion">Recuperación (%)</label>
                                        <input type="text" class="form-control" id="veracidad_recuperacion" name="veracidad_recuperacion" value="" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="veracidad_estado">Estado Veracidad</label>
                                        <input type="text" class="form-control" id="veracidad_estado" name="veracidad_estado" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="veracidad_observaciones">Observaciones de Veracidad</label>
                                        <input type="text" class="form-control" id="veracidad_observaciones" name="veracidad_observaciones" value="{{ old('veracidad_observaciones', $firstAnalysis->cationExchangeAnalysis->veracidad_analitica['veracidad_observaciones'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Muestra de Referencia Certificada -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="muestra_referencia_certificada_valor_teorico">Muestra de Referencia Certificada - Valor Teórico (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('muestra_referencia_certificada_valor_teorico') is-invalid @enderror" id="muestra_referencia_certificada_valor_teorico" name="muestra_referencia_certificada_valor_teorico" value="{{ old('muestra_referencia_certificada_valor_teorico', $firstAnalysis->cationExchangeAnalysis->muestra_referencia_certificada_analitica['valor_teorico'] ?? '') }}" required>
                                        @error('muestra_referencia_certificada_valor_teorico')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="muestra_referencia_certificada_valor_leido">Muestra de Referencia Certificada - Valor Leído (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('muestra_referencia_certificada_valor_leido') is-invalid @enderror" id="muestra_referencia_certificada_valor_leido" name="muestra_referencia_certificada_valor_leido" value="{{ old('muestra_referencia_certificada_valor_leido', $firstAnalysis->cationExchangeAnalysis->muestra_referencia_certificada_analitica['valor_leido'] ?? '') }}" required>
                                        @error('muestra_referencia_certificada_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mrc_error_porcentaje">% Error</label>
                                        <input type="text" class="form-control" id="mrc_error_porcentaje" name="mrc_error_porcentaje" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mrc_estado">Estado MRC</label>
                                        <input type="text" class="form-control" id="mrc_estado" name="mrc_estado" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Muestra de Referencia -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="muestra_referencia_valor_teorico">Muestra de Referencia - Valor Teórico (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('muestra_referencia_valor_teorico') is-invalid @enderror" id="muestra_referencia_valor_teorico" name="muestra_referencia_valor_teorico" value="{{ old('muestra_referencia_valor_teorico', $firstAnalysis->cationExchangeAnalysis->veracidad_analitica['mr_valor_teorico'] ?? '') }}" required>
                                        @error('muestra_referencia_valor_teorico')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="muestra_referencia_valor_leido">Muestra de Referencia - Valor Leído (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('muestra_referencia_valor_leido') is-invalid @enderror" id="muestra_referencia_valor_leido" name="muestra_referencia_valor_leido" value="{{ old('muestra_referencia_valor_leido', $firstAnalysis->cationExchangeAnalysis->veracidad_analitica['mr_valor_leido'] ?? '') }}" required>
                                        @error('muestra_referencia_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mr_recuperacion_porcentaje">% Recuperación</label>
                                        <input type="text" class="form-control" id="mr_recuperacion_porcentaje" name="mr_recuperacion_porcentaje" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="mr_estado">Estado MR</label>
                                        <input type="text" class="form-control" id="mr_estado" name="mr_estado" value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Precisión Analítica (Duplicados) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Precisión Analítica (Duplicados)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_a_valor_leido">Duplicado A - Valor Leído (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('duplicado_a_valor_leido') is-invalid @enderror" id="duplicado_a_valor_leido" name="duplicado_a_valor_leido" value="{{ old('duplicado_a_valor_leido', $firstAnalysis->cationExchangeAnalysis->precision_analitica['duplicado_a_valor_leido'] ?? '') }}" required>
                                        @error('duplicado_a_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_b_valor_leido">Duplicado B - Valor Leído (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('duplicado_b_valor_leido') is-invalid @enderror" id="duplicado_b_valor_leido" name="duplicado_b_valor_leido" value="{{ old('duplicado_b_valor_leido', $firstAnalysis->cationExchangeAnalysis->precision_analitica['duplicado_b_valor_leido'] ?? '') }}" required>
                                        @error('duplicado_b_valor_leido')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_diferencia">Diferencia (meq/100g)</label>
                                        <input type="text" class="form-control" id="duplicado_diferencia" name="duplicado_diferencia" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_porcentaje">% Diferencia</label>
                                        <input type="text" class="form-control" id="duplicado_porcentaje" name="duplicado_porcentaje" value="" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_estado">Estado Duplicados</label>
                                        <input type="text" class="form-control" id="duplicado_estado" name="duplicado_estado" value="" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="duplicado_observaciones">Observaciones de Duplicados</label>
                                        <input type="text" class="form-control" id="duplicado_observaciones" name="duplicado_observaciones" value="{{ old('duplicado_observaciones', $firstAnalysis->cationExchangeAnalysis->precision_analitica['duplicado_observaciones'] ?? '') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Duplicados (DPR) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dpr_replica_1">DPR - Réplica 1 (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('dpr_replica_1') is-invalid @enderror" id="dpr_replica_1" name="dpr_replica_1" value="{{ old('dpr_replica_1', $firstAnalysis->cationExchangeAnalysis->precision_analitica['dpr_replica_1'] ?? '') }}" required>
                                        @error('dpr_replica_1')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dpr_replica_2">DPR - Réplica 2 (meq/100g)</label>
                                        <input type="number" step="0.01" class="form-control @error('dpr_replica_2') is-invalid @enderror" id="dpr_replica_2" name="dpr_replica_2" value="{{ old('dpr_replica_2', $firstAnalysis->cationExchangeAnalysis->precision_analitica['dpr_replica_2'] ?? '') }}" required>
                                        @error('dpr_replica_2')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dpr_rpd_porcentaje">RPD (%)</label>
                                        <input type="text" class="form-control" id="dpr_rpd_porcentaje" name="dpr_rpd_porcentaje" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="dpr_estado">Estado DPR</label>
                                        <input type="text" class="form-control" id="dpr_estado" name="dpr_estado" value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Ítems de Ensayo -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ítems de Ensayo</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                Complete los valores para los análisis seleccionados. El "Valor Leído (CIC)" se calculará automáticamente.
                            </div>
                            <table class="table table-bordered" id="items_ensayo_table">
                                <thead>
                                    <tr>
                                        <th>ID Análisis</th>
                                        <th>Proceso</th>
                                        <th>Identificación Muestra</th>
                                        <th>Peso (g)</th>
                                        <th>Vol. NaOH (Muestra) (mL)</th>
                                        <th>Vol. NaOH (Blanco) (mL)</th>
                                        <th>Humedad (%)</th>
                                        <th>Valor Leído (CIC) (meq/100g)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingAnalyses as $analysis)
                                        @foreach ($analysis->items_ensayo ?? [] as $index => $item)
                                            <tr>
                                                <td>{{ $analysis->id }}<input type="hidden" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][analysis_id]" value="{{ $analysis->id }}"></td>
                                                <td>{{ $analysis->process->process_id }}</td>
                                                <td><input type="text" class="form-control" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][identificacion]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.identificacion', $item['identificacion'] ?? 'Muestra ' . ($loop->parent->index + 1) . '-' . ($index + 1)) }}"></td>
                                                <td><input type="number" step="0.0001" class="form-control peso-input" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][peso]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.peso', $item['peso'] ?? '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control vol-naoh-muestra-input" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][vol_naoh_muestra]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.vol_naoh_muestra', $item['vol_naoh_muestra'] ?? '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control vol-naoh-blanco-input" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][vol_naoh_blanco]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.vol_naoh_blanco', $item['vol_naoh_blanco'] ?? '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control humedad-input" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][humedad]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.humedad', $item['humedad'] ?? '') }}" required></td>
                                                <td><input type="text" class="form-control valor-leido-cic" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][valor_leido]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.valor_leido', $item['valor_leido'] ?? '') }}" readonly></td>
                                                <td><input type="text" class="form-control" name="items_ensayo[{{$loop->parent->index}}][{{$index}}][observaciones]" value="{{ old('items_ensayo.' . $loop->parent->index . '.' . $index . '.observaciones', $item['observaciones'] ?? '') }}"></td>
                                            </tr>
                                        @endforeach
                                        @if (empty($analysis->items_ensayo) || collect($analysis->items_ensayo)->every(fn($i) => isset($i['valor_leido']) && $i['valor_leido'] !== ''))
                                            {{-- Si no hay items de ensayo o todos están completados, añade una fila por defecto para este análisis --}}
                                            <tr>
                                                <td>{{ $analysis->id }}<input type="hidden" name="items_ensayo[{{$loop->index}}][0][analysis_id]" value="{{ $analysis->id }}"></td>
                                                <td>{{ $analysis->process->process_id }}</td>
                                                <td><input type="text" class="form-control" name="items_ensayo[{{$loop->index}}][0][identificacion]" value="{{ old('items_ensayo.' . $loop->index . '.0.identificacion', 'Muestra ' . ($loop->index + 1)) }}"></td>
                                                <td><input type="number" step="0.0001" class="form-control peso-input" name="items_ensayo[{{$loop->index}}][0][peso]" value="{{ old('items_ensayo.' . $loop->index . '.0.peso', '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control vol-naoh-muestra-input" name="items_ensayo[{{$loop->index}}][0][vol_naoh_muestra]" value="{{ old('items_ensayo.' . $loop->index . '.0.vol_naoh_muestra', '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control vol-naoh-blanco-input" name="items_ensayo[{{$loop->index}}][0][vol_naoh_blanco]" value="{{ old('items_ensayo.' . $loop->index . '.0.vol_naoh_blanco', '') }}" required></td>
                                                <td><input type="number" step="0.01" class="form-control humedad-input" name="items_ensayo[{{$loop->index}}][0][humedad]" value="{{ old('items_ensayo.' . $loop->index . '.0.humedad', '') }}" required></td>
                                                <td><input type="text" class="form-control valor-leido-cic" name="items_ensayo[{{$loop->index}}][0][valor_leido]" value="{{ old('items_ensayo.' . $loop->index . '.0.valor_leido', '') }}" readonly></td>
                                                <td><input type="text" class="form-control" name="items_ensayo[{{$loop->index}}][0][observaciones]" value="{{ old('items_ensayo.' . $loop->index . '.0.observaciones', '') }}"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sección: Observaciones Generales -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Observaciones Generales</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $firstAnalysis->cationExchangeAnalysis->observaciones ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Análisis de Intercambio Catiónico</button>
                </form>
            @endif
        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function calculateCIC() {
            $('#items_ensayo_table tbody tr').each(function() {
                var peso = parseFloat($(this).find('.peso-input').val());
                var volNaohMuestra = parseFloat($(this).find('.vol-naoh-muestra-input').val());
                var volNaohBlanco = parseFloat($(this).find('.vol-naoh-blanco-input').val());
                var humedad = parseFloat($(this).find('.humedad-input').val());
                var normalidadNaoh = parseFloat($('#normalidad_naoh').val());

                if (!isNaN(peso) && !isNaN(volNaohMuestra) && !isNaN(volNaohBlanco) && !isNaN(humedad) && !isNaN(normalidadNaoh)) {
                    var denominador = peso * (1 - (humedad / 100));
                    if (denominador === 0) {
                        $(this).find('.valor-leido-cic').val('Error: Peso/Humedad 0');
                    } else {
                        var cic = ((volNaohMuestra - volNaohBlanco) * normalidadNaoh * 100) / denominador;
                        $(this).find('.valor-leido-cic').val(cic.toFixed(4));
                    }
                } else {
                    $(this).find('.valor-leido-cic').val('');
                }
            });
        }

        function calculateBlancoProcess() {
            var blancoValorLeido = parseFloat($('#blanco_valor_leido').val());
            if (!isNaN(blancoValorLeido)) {
                var estado = (blancoValorLeido <= 0.1) ? 'Aceptable' : 'No Aceptable';
                $('#blanco_estado').val(estado);
            } else {
                $('#blanco_estado').val('');
            }
        }

        function calculateVeracidadAnalitica() {
            var esperado = parseFloat($('#veracidad_valor_esperado').val());
            var leido = parseFloat($('#veracidad_valor_leido').val());
            if (!isNaN(esperado) && !isNaN(leido) && esperado > 0) {
                var recuperacion = (leido / esperado) * 100;
                $('#veracidad_recuperacion').val(recuperacion.toFixed(2) + '%');
                var estado = (recuperacion >= 70 && recuperacion <= 130) ? 'Aceptable' : 'No Aceptable';
                $('#veracidad_estado').val(estado);
            } else {
                $('#veracidad_recuperacion').val('');
                $('#veracidad_estado').val('');
            }
        }

        function calculateMuestraReferenciaCertificada() {
            var teorico = parseFloat($('#muestra_referencia_certificada_valor_teorico').val());
            var leido = parseFloat($('#muestra_referencia_certificada_valor_leido').val());
            if (!isNaN(teorico) && !isNaN(leido) && teorico > 0) {
                var error = (Math.abs(leido - teorico) / teorico) * 100;
                $('#mrc_error_porcentaje').val(error.toFixed(2) + '%');
                var estado = (error <= 10) ? 'Aceptable' : 'No Aceptable';
                $('#mrc_estado').val(estado);
            } else {
                $('#mrc_error_porcentaje').val('');
                $('#mrc_estado').val('');
            }
        }

        function calculateMuestraReferencia() {
            var teorico = parseFloat($('#muestra_referencia_valor_teorico').val());
            var leido = parseFloat($('#muestra_referencia_valor_leido').val());
            if (!isNaN(teorico) && !isNaN(leido) && teorico > 0) {
                var recuperacion = (leido / teorico) * 100;
                $('#mr_recuperacion_porcentaje').val(recuperacion.toFixed(2) + '%');
                var estado = (recuperacion >= 90 && recuperacion <= 110) ? 'Aceptable' : 'No Aceptable';
                $('#mr_estado').val(estado);
            } else {
                $('#mr_recuperacion_porcentaje').val('');
                $('#mr_estado').val('');
            }
        }

        function calculateDuplicados() {
            var a = parseFloat($('#duplicado_a_valor_leido').val());
            var b = parseFloat($('#duplicado_b_valor_leido').val());
            if (!isNaN(a) && !isNaN(b)) {
                var diferencia = Math.abs(a - b);
                var promedio = (a + b) / 2;
                var porcentaje = promedio > 0 ? (diferencia / promedio) * 100 : 0;
                $('#duplicado_diferencia').val(diferencia.toFixed(4));
                $('#duplicado_porcentaje').val(porcentaje.toFixed(2) + '%');
                var estado = (porcentaje <= 10) ? 'Aceptable' : 'No Aceptable';
                $('#duplicado_estado').val(estado);
            } else {
                $('#duplicado_diferencia').val('');
                $('#duplicado_porcentaje').val('');
                $('#duplicado_estado').val('');
            }
        }

        function calculateDPR() {
            var replica1 = parseFloat($('#dpr_replica_1').val());
            var replica2 = parseFloat($('#dpr_replica_2').val());
            if (!isNaN(replica1) && !isNaN(replica2)) {
                var promedio = (replica1 + replica2) / 2;
                var rpd = promedio > 0 ? (Math.abs(replica1 - replica2) / promedio) * 100 : 0;
                $('#dpr_rpd_porcentaje').val(rpd.toFixed(2) + '%');
                var estado = (rpd <= 20) ? 'Aceptable' : 'No Aceptable';
                $('#dpr_estado').val(estado);
            } else {
                $('#dpr_rpd_porcentaje').val('');
                $('#dpr_estado').val('');
            }
        }

        // Event Listeners
        $('#normalidad_naoh, .peso-input, .vol-naoh-muestra-input, .vol-naoh-blanco-input, .humedad-input').on('input', calculateCIC);
        $('#blanco_valor_leido').on('input', calculateBlancoProcess);
        $('#veracidad_valor_esperado, #veracidad_valor_leido').on('input', calculateVeracidadAnalitica);
        $('#muestra_referencia_certificada_valor_teorico, #muestra_referencia_certificada_valor_leido').on('input', calculateMuestraReferenciaCertificada);
        $('#muestra_referencia_valor_teorico, #muestra_referencia_valor_leido').on('input', calculateMuestraReferencia);
        $('#duplicado_a_valor_leido, #duplicado_b_valor_leido').on('input', calculateDuplicados);
        $('#dpr_replica_1, #dpr_replica_2').on('input', calculateDPR);

        // Initial calculations on page load
        calculateCIC();
        calculateBlancoProcess();
        calculateVeracidadAnalitica();
        calculateMuestraReferenciaCertificada();
        calculateMuestraReferencia();
        calculateDuplicados();
        calculateDPR();
    });
</script>
@endpush 