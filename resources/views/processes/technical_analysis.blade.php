@extends('layouts.master')

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Análisis para Proceso {{ $process->item_code }}</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('success') }}
        </div>
        @endif

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

        @foreach ($analysisForms as $serviceId => $formData)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Servicio: {{ $formData['service']->description }}</h3>
            </div>
            <div class="card-body">
                <h4>Análisis Existentes</h4>
                @if ($formData['existing_analyses']->isNotEmpty())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha del Análisis</th>
                            <th>Detalles</th>
                            <th>Estado de Revisión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($formData['existing_analyses'] as $analysis)
                        <tr>
                            <td>{{ $analysis->analysis_date }}</td>
                            <td>
                                <ul>
                                    @foreach ($analysis->details as $key => $value)
                                    <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                @if ($analysis->review_status === 'pending')
                                <span class="badge badge-warning">Pendiente</span>
                                @elseif ($analysis->review_status === 'approved')
                                <span class="badge badge-success">Aprobado</span>
                                @else
                                <span class="badge badge-danger">Rechazado</span>
                                @if ($analysis->review_observations)
                                <p><strong>Observaciones:</strong> {{ $analysis->review_observations }}</p>
                                @endif
                                @endif
                            </td>
                            <td>
                                @if ($analysis->review_status === 'rejected')
                                <a href="{{ route('process.edit_analysis', $analysis->id) }}" class="btn btn-warning btn-sm">Corregir</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p>No hay análisis registrados.</p>
                @endif

                <h4>Nuevo Análisis (Máximo 20 muestras por día)</h4>
                <form action="{{ route('process.store_analysis', [$process->process_id, $serviceId]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_type" value="{{ $formData['form_type'] }}">

                    <div class="form-group">
                        <label for="analysis_date_{{ $serviceId }}">Fecha del Análisis:</label>
                        <input type="date" name="analysis_date" id="analysis_date_{{ $serviceId }}" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="analyst_name_{{ $serviceId }}">Nombre del Analista:</label>
                        <input type="text" name="analyst_name" id="analyst_name_{{ $serviceId }}" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>

                    <div id="samples_container_{{ $serviceId }}">
                        <!-- Campos dinámicos para las muestras -->
                        <div class="sample_fields" data-index="0">
                            <h5>Muestra 1</h5>
                            @if ($formData['form_type'] === 'acidez')
                            <!-- Formulario para Acidez Intercambiable -->
                            <div class="form-group">
                                <label for="internal_code_{{ $serviceId }}_0">Código Interno:</label>
                                <input type="text" name="samples[0][internal_code]" id="internal_code_{{ $serviceId }}_0" class="form-control @error('samples.0.internal_code') is-invalid @enderror" required>
                                @error('samples.0.internal_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="weight_{{ $serviceId }}_0">Peso (g):</label>
                                <input type="number" step="0.01" name="samples[0][weight]" id="weight_{{ $serviceId }}_0" class="form-control @error('samples.0.weight') is-invalid @enderror" required>
                                @error('samples.0.weight')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_blank_{{ $serviceId }}_0">Vol NaOH Consumido Blanco (mL):</label>
                                <input type="number" step="0.01" name="samples[0][naoh_blank]" id="naoh_blank_{{ $serviceId }}_0" class="form-control @error('samples.0.naoh_blank') is-invalid @enderror" required>
                                @error('samples.0.naoh_blank')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="humidity_{{ $serviceId }}_0">% Humedad (g/100g):</label>
                                <input type="number" step="0.01" name="samples[0][humidity]" id="humidity_{{ $serviceId }}_0" class="form-control @error('samples.0.humidity') is-invalid @enderror" required>
                                @error('samples.0.humidity')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_molarity_{{ $serviceId }}_0">Molaridad NaOH:</label>
                                <input type="number" step="0.01" name="samples[0][naoh_molarity]" id="naoh_molarity_{{ $serviceId }}_0" class="form-control @error('samples.0.naoh_molarity') is-invalid @enderror" required>
                                @error('samples.0.naoh_molarity')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="naoh_sample_{{ $serviceId }}_0">Vol NaOH Consumido Muestra (mL):</label>
                                <input type="number" step="0.01" name="samples[0][naoh_sample]" id="naoh_sample_{{ $serviceId }}_0" class="form-control @error('samples.0.naoh_sample') is-invalid @enderror" required>
                                @error('samples.0.naoh_sample')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="observations_{{ $serviceId }}_0">Observaciones:</label>
                                <textarea name="samples[0][observations]" id="observations_{{ $serviceId }}_0" class="form-control @error('samples.0.observations') is-invalid @enderror"></textarea>
                                @error('samples.0.observations')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            @elseif ($formData['form_type'] === 'ph')
                            <!-- Formulario para pH -->
                            <div class="form-group">
                                <label for="internal_code_{{ $serviceId }}_0">Código Interno:</label>
                                <input type="text" name="samples[0][internal_code]" id="internal_code_{{ $serviceId }}_0" class="form-control @error('samples.0.internal_code') is-invalid @enderror" required>
                                @error('samples.0.internal_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="weight_{{ $serviceId }}_0">Peso (g):</label>
                                <input type="number" step="0.01" name="samples[0][weight]" id="weight_{{ $serviceId }}_0" class="form-control @error('samples.0.weight') is-invalid @enderror" required>
                                @error('samples.0.weight')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="water_volume_{{ $serviceId }}_0">Volumen H₂O Destilada (mL):</label>
                                <input type="number" step="0.01" name="samples[0][water_volume]" id="water_volume_{{ $serviceId }}_0" class="form-control @error('samples.0.water_volume') is-invalid @enderror" required>
                                @error('samples.0.water_volume')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="temperature_{{ $serviceId }}_0">Temperatura (°C):</label>
                                <input type="number" step="0.1" name="samples[0][temperature]" id="temperature_{{ $serviceId }}_0" class="form-control @error('samples.0.temperature') is-invalid @enderror" required>
                                @error('samples.0.temperature')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="ph_value_{{ $serviceId }}_0">Valor Leído (Unidades de pH):</label>
                                <input type="number" step="0.001" name="samples[0][ph_value]" id="ph_value_{{ $serviceId }}_0" class="form-control @error('samples.0.ph_value') is-invalid @enderror" required>
                                @error('samples.0.ph_value')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="observations_{{ $serviceId }}_0">Observaciones:</label>
                                <textarea name="samples[0][observations]" id="observations_{{ $serviceId }}_0" class="form-control @error('samples.0.observations') is-invalid @enderror"></textarea>
                                @error('samples.0.observations')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            @elseif ($formData['form_type'] === 'conductividad')
                            <!-- Formulario para Conductividad Eléctrica -->
                            <div class="form-group">
                                <label for="internal_code_{{ $serviceId }}_0">Código Interno Muestra:</label>
                                <input type="text" name="samples[0][internal_code]" id="internal_code_{{ $serviceId }}_0" class="form-control @error('samples.0.internal_code') is-invalid @enderror" required>
                                @error('samples.0.internal_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="weight_{{ $serviceId }}_0">Peso de Muestra (g):</label>
                                <input type="number" step="0.01" name="samples[0][weight]" id="weight_{{ $serviceId }}_0" class="form-control @error('samples.0.weight') is-invalid @enderror" required>
                                @error('samples.0.weight')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="water_volume_{{ $serviceId }}_0">Volumen H₂O Destilada (mL):</label>
                                <input type="number" step="0.01" name="samples[0][water_volume]" id="water_volume_{{ $serviceId }}_0" class="form-control @error('samples.0.water_volume') is-invalid @enderror" required>
                                @error('samples.0.water_volume')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="temperature_{{ $serviceId }}_0">Temperatura Extracto (°C):</label>
                                <input type="number" step="0.1" name="samples[0][temperature]" id="temperature_{{ $serviceId }}_0" class="form-control @error('samples.0.temperature') is-invalid @enderror" required>
                                @error('samples.0.temperature')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="conductivity_uscm_{{ $serviceId }}_0">Lectura (µS/cm):</label>
                                <input type="number" step="0.01" name="samples[0][conductivity_uscm]" id="conductivity_uscm_{{ $serviceId }}_0" class="form-control @error('samples.0.conductivity_uscm') is-invalid @enderror" required>
                                @error('samples.0.conductivity_uscm')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="observations_{{ $serviceId }}_0">Observaciones:</label>
                                <textarea name="samples[0][observations]" id="observations_{{ $serviceId }}_0" class="form-control @error('samples.0.observations') is-invalid @enderror"></textarea>
                                @error('samples.0.observations')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary mb-3" onclick="addSample({{ $serviceId }}, '{{ $formData['form_type'] }}')">Agregar Muestra</button>

                    <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                    <a href="{{ route('process.technical_index') }}" class="btn btn-secondary">Volver</a>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</section>

<script>
    let sampleCounts = {};

    function addSample(serviceId, formType) {
        if (!sampleCounts[serviceId]) {
            sampleCounts[serviceId] = 1;
        } else {
            sampleCounts[serviceId]++;
        }

        if (sampleCounts[serviceId] >= 20) {
            alert('No se pueden agregar más de 20 muestras por día.');
            return;
        }

        const index = sampleCounts[serviceId];
        const container = document.getElementById(`samples_container_${serviceId}`);
        const newSampleDiv = document.createElement('div');
        newSampleDiv.className = 'sample_fields';
        newSampleDiv.setAttribute('data-index', index);

        let html = `<h5>Muestra ${index + 1}</h5>`;

        if (formType === 'acidez') {
            html += `
                    <div class="form-group">
                        <label for="internal_code_${serviceId}_${index}">Código Interno:</label>
                        <input type="text" name="samples[${index}][internal_code]" id="internal_code_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="weight_${serviceId}_${index}">Peso (g):</label>
                        <input type="number" step="0.01" name="samples[${index}][weight]" id="weight_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="naoh_blank_${serviceId}_${index}">Vol NaOH Consumido Blanco (mL):</label>
                        <input type="number" step="0.01" name="samples[${index}][naoh_blank]" id="naoh_blank_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="humidity_${serviceId}_${index}">% Humedad (g/100g):</label>
                        <input type="number" step="0.01" name="samples[${index}][humidity]" id="humidity_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="naoh_molarity_${serviceId}_${index}">Molaridad NaOH:</label>
                        <input type="number" step="0.01" name="samples[${index}][naoh_molarity]" id="naoh_molarity_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="naoh_sample_${serviceId}_${index}">Vol NaOH Consumido Muestra (mL):</label>
                        <input type="number" step="0.01" name="samples[${index}][naoh_sample]" id="naoh_sample_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="observations_${serviceId}_${index}">Observaciones:</label>
                        <textarea name="samples[${index}][observations]" id="observations_${serviceId}_${index}" class="form-control"></textarea>
                    </div>
                `;
        } else if (formType === 'ph') {
            html += `
                    <div class="form-group">
                        <label for="internal_code_${serviceId}_${index}">Código Interno:</label>
                        <input type="text" name="samples[${index}][internal_code]" id="internal_code_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="weight_${serviceId}_${index}">Peso (g):</label>
                        <input type="number" step="0.01" name="samples[${index}][weight]" id="weight_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="water_volume_${serviceId}_${index}">Volumen H₂O Destilada (mL):</label>
                        <input type="number" step="0.01" name="samples[${index}][water_volume]" id="water_volume_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="temperature_${serviceId}_${index}">Temperatura (°C):</label>
                        <input type="number" step="0.1" name="samples[${index}][temperature]" id="temperature_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="ph_value_${serviceId}_${index}">Valor Leído (Unidades de pH):</label>
                        <input type="number" step="0.001" name="samples[${index}][ph_value]" id="ph_value_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="observations_${serviceId}_${index}">Observaciones:</label>
                        <textarea name="samples[${index}][observations]" id="observations_${serviceId}_${index}" class="form-control"></textarea>
                    </div>
                `;
        } else if (formType === 'conductividad') {
            html += `
                    <div class="form-group">
                        <label for="internal_code_${serviceId}_${index}">Código Interno Muestra:</label>
                        <input type="text" name="samples[${index}][internal_code]" id="internal_code_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="weight_${serviceId}_${index}">Peso de Muestra (g):</label>
                        <input type="number" step="0.01" name="samples[${index}][weight]" id="weight_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="water_volume_${serviceId}_${index}">Volumen H₂O Destilada (mL):</label>
                        <input type="number" step="0.01" name="samples[${index}][water_volume]" id="water_volume_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="temperature_${serviceId}_${index}">Temperatura Extracto (°C):</label>
                        <input type="number" step="0.1" name="samples[${index}][temperature]" id="temperature_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="conductivity_uscm_${serviceId}_${index}">Lectura (µS/cm):</label>
                        <input type="number" step="0.01" name="samples[${index}][conductivity_uscm]" id="conductivity_uscm_${serviceId}_${index}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="observations_${serviceId}_${index}">Observaciones:</label>
                        <textarea name="samples[${index}][observations]" id="observations_${serviceId}_${index}" class="form-control"></textarea>
                    </div>
                `;
        }

        newSampleDiv.innerHTML = html;
        container.appendChild(newSampleDiv);
    }
</script>
@endsection