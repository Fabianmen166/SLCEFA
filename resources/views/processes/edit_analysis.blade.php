@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Corregir Análisis: {{ $analysis->details['internal_code'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($analysis->review_observations)
                <div class="alert alert-warning">
                    <strong>Observaciones de la Revisión:</strong> {{ $analysis->review_observations }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Corrección</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('process.update_analysis', $analysis->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="form_type" value="{{ $formType }}">

                        <div class="form-group">
                            <label for="analysis_date">Fecha del Análisis:</label>
                            <input type="date" name="analysis_date" id="analysis_date" class="form-control" value="{{ $analysis->analysis_date->format('Y-m-d') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="analyst_name">Nombre del Analista:</label>
                            <input type="text" name="analyst_name" id="analyst_name" class="form-control" value="{{ $analysis->details['analyst_name'] }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="internal_code">Código Interno:</label>
                            <input type="text" name="internal_code" id="internal_code" class="form-control @error('internal_code') is-invalid @enderror" value="{{ $analysis->details['internal_code'] }}" required>
                            @error('internal_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        @if ($formType === 'acidez')
                            <div class="form-group">
                                <label for="weight">Peso (g):</label>
                                <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ $analysis->details['weight'] }}" required>
                                @error('weight')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_blank">Vol NaOH Consumido Blanco (mL):</label>
                                <input type="number" step="0.01" name="naoh_blank" id="naoh_blank" class="form-control @error('naoh_blank') is-invalid @enderror" value="{{ $analysis->details['naoh_blank'] }}" required>
                                @error('naoh_blank')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="humidity">% Humedad (g/100g):</label>
                                <input type="number" step="0.01" name="humidity" id="humidity" class="form-control @error('humidity') is-invalid @enderror" value="{{ $analysis->details['humidity'] }}" required>
                                @error('humidity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_molarity">Molaridad NaOH:</label>
                                <input type="number" step="0.01" name="naoh_molarity" id="naoh_molarity" class="form-control @error('naoh_molarity') is-invalid @enderror" value="{{ $analysis->details['naoh_molarity'] }}" required>
                                @error('naoh_molarity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_sample">Vol NaOH Consumido Muestra (mL):</label>
                                <input type="number" step="0.01" name="naoh_sample" id="naoh_sample" class="form-control @error('naoh_sample') is-invalid @enderror" value="{{ $analysis->details['naoh_sample'] }}" required>
                                @error('naoh_sample')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif ($formType === 'ph')
                            <div class="form-group">
                                <label for="weight">Peso (g):</label>
                                <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ $analysis->details['weight'] }}" required>
                                @error('weight')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="water_volume">Volumen H₂O Destilada (mL):</label>
                                <input type="number" step="0.01" name="water_volume" id="water_volume" class="form-control @error('water_volume') is-invalid @enderror" value="{{ $analysis->details['water_volume'] }}" required>
                                @error('water_volume')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="temperature">Temperatura (°C):</label>
                                <input type="number" step="0.1" name="temperature" id="temperature" class="form-control @error('temperature') is-invalid @enderror" value="{{ $analysis->details['temperature'] }}" required>
                                @error('temperature')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="ph_value">Valor Leído (Unidades de pH):</label>
                                <input type="number" step="0.001" name="ph_value" id="ph_value" class="form-control @error('ph_value') is-invalid @enderror" value="{{ $analysis->details['ph_value'] }}" required>
                                @error('ph_value')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif ($formType === 'conductividad')
                            <div class="form-group">
                                <label for="weight">Peso de Muestra (g):</label>
                                <input type="number" step="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ $analysis->details['weight'] }}" required>
                                @error('weight')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="water_volume">Volumen H₂O Destilada (mL):</label>
                                <input type="number" step="0.01" name="water_volume" id="water_volume" class="form-control @error('water_volume') is-invalid @enderror" value="{{ $analysis->details['water_volume'] }}" required>
                                @error('water_volume')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="temperature">Temperatura Extracto (°C):</label>
                                <input type="number" step="0.1" name="temperature" id="temperature" class="form-control @error('temperature') is-invalid @enderror" value="{{ $analysis->details['temperature'] }}" required>
                                @error('temperature')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="conductivity_uscm">Lectura (µS/cm):</label>
                                <input type="number" step="0.01" name="conductivity_uscm" id="conductivity_uscm" class="form-control @error('conductivity_uscm') is-invalid @enderror" value="{{ $analysis->details['conductivity_uscm'] }}" required>
                                @error('conductivity_uscm')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="observations">Observaciones:</label>
                            <textarea name="observations" id="observations" class="form-control @error('observations') is-invalid @enderror">{{ $analysis->details['observations'] ?? '' }}</textarea>
                            @error('observations')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Corrección</button>
                        <a href="{{ route('process.technical_analysis', $analysis->process_id) }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection