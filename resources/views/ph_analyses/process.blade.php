@extends('layouts.app')

@section('title', 'Procesar Análisis de pH')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de pH</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('ph_analysis.index') }}">Gestión de pH</a></li>
                        <li class="breadcrumb-item active">Procesar Análisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Main Content -->
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

            {{-- Determine if we are in single analysis or batch analysis context --}}
            @php
                // Ensure $pendingAnalyses is always set, even if null
                if (!isset($pendingAnalyses)) {
                    $pendingAnalyses = null;
                }

                $isBatch = $pendingAnalyses instanceof \Illuminate\Support\Collection;

                // Normalize data structure for the view
                if ($isBatch) {
                    // Data comes from processAll method
                    $displayAnalyses = $pendingAnalyses; // Collection of Analysis models
                    $displayPhAnalysis = null; // No single PhAnalysis for general sections in batch mode
                    $formActionRoute = route('ph_analysis.store'); // Route for batch processing
                } else {
                    // Data comes from phAnalysis method (single analysis)
                    $displayAnalyses = collect([$analysis]); // Wrap the single Analysis model in a collection
                    $displayPhAnalysis = $phAnalysis; // The specific PhAnalysis model for this single analysis
                    $formActionRoute = route('ph_analysis.store_ph_analysis', ['processId' => $process->process_id, 'serviceId' => $service->services_id]); // Route for single analysis
                }

                // Determine the primary analysis to pull general info from (first in batch, or the single one)
                $firstAnalysis = $displayAnalyses->first();
            @endphp

            @if ($displayAnalyses->isEmpty())
                <div class="alert alert-danger">
                    Error: No hay análisis de pH pendientes para procesar.
                </div>
                <a href="{{ route('ph_analysis.index') }}" class="btn btn-secondary">Regresar</a>
            @else
                <form action="{{ $formActionRoute }}" method="POST" id="phAnalysisForm">
                    @csrf

                    <!-- Hidden Input for Analysis IDs -->
                    @foreach ($displayAnalyses as $analysis_item)
                        <input type="hidden" name="analyses[{{$loop->index}}][analysis_id]" value="{{$analysis_item->id}}">
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
                                        <input type="text" class="form-control" id="process_id" value="{{ $displayAnalyses->pluck('process.process_id')->unique()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="service">Servicios Involucrados</label>
                                        <input type="text" class="form-control" id="service" value="{{ $displayAnalyses->pluck('service.descripcion')->unique()->implode(', ') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="consecutivo">Consecutivo No.</label>
                                        <input type="text" class="form-control @error('consecutivo_no') is-invalid @enderror" id="consecutivo" name="consecutivo_no" value="{{ old('consecutivo_no', $displayPhAnalysis->consecutivo_no ?? '') }}" required>
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
                                        <input type="date" class="form-control @error('fecha_analisis') is-invalid @enderror" id="fecha_analisis" name="fecha_analisis" value="{{ old('fecha_analisis', $displayPhAnalysis->fecha_analisis ?? now()->format('Y-m-d')) }}" required>
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
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Detalles del Equipo -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Detalles del Equipo</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_probeta">Código de la Probeta</label>
                                        <input type="text" class="form-control @error('codigo_probeta') is-invalid @enderror" id="codigo_probeta" name="codigo_probeta" value="{{ old('codigo_probeta', $displayPhAnalysis->codigo_probeta ?? '') }}" required>
                                        @error('codigo_probeta')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_equipo">Código del Equipo Potenciométrico</label>
                                        <input type="text" class="form-control @error('codigo_equipo') is-invalid @enderror" id="codigo_equipo" name="codigo_equipo" value="{{ old('codigo_equipo', $displayPhAnalysis->codigo_equipo ?? '') }}" required>
                                        @error('codigo_equipo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_electrodo">Serial del Electrodo</label>
                                        <input type="text" class="form-control @error('serial_electrodo') is-invalid @enderror" id="serial_electrodo" name="serial_electrodo" value="{{ old('serial_electrodo', $displayPhAnalysis->serial_electrodo ?? '') }}" required>
                                        @error('serial_electrodo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_sonda">Serial de la Sonda de Temperatura</label>
                                        <input type="text" class="form-control @error('serial_sonda_temperatura') is-invalid @enderror" id="serial_sonda" name="serial_sonda_temperatura" value="{{ old('serial_sonda_temperatura', $displayPhAnalysis->serial_sonda_temperatura ?? '') }}" required>
                                        @error('serial_sonda_temperatura')
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
                            <table class="table table-bordered" id="controles_analiticos">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Lote</th>
                                        <th>Valor Leído (pH)</th>
                                        <th>Valor Esperado (pH)</th>
                                        <th>% Error</th>
                                        <th>Aceptabilidad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $controlCount = $displayAnalyses->count() > 1 ? 4 : 1;
                                    @endphp
                                    @for ($index = 0; $index < $controlCount; $index++)
                                        @php
                                            $identificaciones = ['Buffer de pH 4', 'Buffer de pH 7', 'Buffer de pH 10', 'Muestra de referencia o MRC'];
                                            $identificacion = $identificaciones[$index] ?? $identificaciones[0];
                                        @endphp
                                        <tr class="control-row">
                                            <td><input type="text" class="form-control control-identificacion" name="controles_analiticos[{{$index}}][identificacion]" value="{{ $identificacion }}" readonly></td>
                                            <td><input type="text" class="form-control control-lote @error('controles_analiticos.' . $index . '.lote') is-invalid @enderror" name="controles_analiticos[{{$index}}][lote]" value="{{ old('controles_analiticos.' . $index . '.lote', $displayPhAnalysis->controles_analiticos[$index]['lote'] ?? '') }}"></td>
                                            <td><input type="number" step="0.01" class="form-control control-valor-leido @error('controles_analiticos.' . $index . '.valor_leido') is-invalid @enderror" name="controles_analiticos[{{$index}}][valor_leido]" value="{{ old('controles_analiticos.' . $index . '.valor_leido', $displayPhAnalysis->controles_analiticos[$index]['valor_leido'] ?? '') }}" data-index="{{$index}}"></td>
                                            <td><input type="number" step="0.01" class="form-control control-valor-esperado @error('controles_analiticos.' . $index . '.valor_esperado') is-invalid @enderror" name="controles_analiticos[{{$index}}][valor_esperado]" value="{{ old('controles_analiticos.' . $index . '.valor_esperado', $displayPhAnalysis->controles_analiticos[$index]['valor_esperado'] ?? '') }}" data-index="{{$index}}"></td>
                                            <td><span class="form-control-plaintext" id="error_{{$index}}"></span></td>
                                            <td><span class="form-control-plaintext" id="aceptabilidad_{{$index}}"></span></td>
                                            <td><input type="text" class="form-control @error('controles_analiticos.' . $index . '.observaciones') is-invalid @enderror" name="controles_analiticos[{{$index}}][observaciones]" value="{{ old('controles_analiticos.' . $index . '.observaciones', $displayPhAnalysis->controles_analiticos[$index]['observaciones'] ?? '') }}"></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                            <div id="controles-error" class="text-danger" style="display: none;">Debe completar al menos un control analítico y todos los completados deben ser aceptables.</div>
                        </div>
                    </div>

                    <!-- Sección: Precisión Analítica -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Precisión Analítica (Muestras por Duplicado)</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered" id="precision_analitica_table">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Peso (g)</th>
                                        <th>Volumen de Agua (mL)</th>
                                        <th>Temperatura (°C)</th>
                                        <th>Valor Leído (pH)</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop through pendingItems --}}
                                    @forelse($pendingItems as $itemIndex => $item)
                                        <tr class="item-row" data-analysis-id="{{ $item['analysis_id'] }}">
                                            <td><input type="text" class="form-control" name="items[{{ $itemIndex }}][identificacion]" value="{{ old('items.' . $itemIndex . '.identificacion', $item['identificacion'] ?? '') }}" required></td>
                                            <td><input type="number" step="0.001" class="form-control" name="items[{{ $itemIndex }}][peso]" value="{{ old('items.' . $itemIndex . '.peso', $item['peso'] ?? '') }}" required></td>
                                            <td><input type="number" step="0.01" class="form-control" name="items[{{ $itemIndex }}][volumen_agua]" value="{{ old('items.' . $itemIndex . '.volumen_agua', $item['volumen_agua'] ?? '') }}" required></td>
                                            <td><input type="number" step="0.1" class="form-control" name="items[{{ $itemIndex }}][temperatura]" value="{{ old('items.' . $itemIndex . '.temperatura', $item['temperatura'] ?? '') }}" required></td>
                                            <td><input type="number" step="0.01" class="form-control" name="items[{{ $itemIndex }}][valor_leido]" value="{{ old('items.' . $itemIndex . '.valor_leido', $item['valor_leido'] ?? '') }}" required></td>
                                            <td><input type="text" class="form-control" name="items[{{ $itemIndex }}][observaciones]" value="{{ old('items.' . $itemIndex . '.observaciones', $item['observaciones'] ?? '') }}"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
                                        </tr>
                                    @empty
                                        {{-- Render at least one empty row if no pending items exist --}}
                                        <tr class="item-row" data-analysis-id="">
                                            <td><input type="text" class="form-control" name="items[0][identificacion]" value="Muestra 1" required></td>
                                            <td><input type="number" step="0.001" class="form-control" name="items[0][peso]" value="" required></td>
                                            <td><input type="number" step="0.01" class="form-control" name="items[0][volumen_agua]" value="" required></td>
                                            <td><input type="number" step="0.1" class="form-control" name="items[0][temperatura]" value="" required></td>
                                            <td><input type="number" step="0.01" class="form-control" name="items[0][valor_leido]" value="" required></td>
                                            <td><input type="text" class="form-control" name="items[0][observaciones]" value=""></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success" id="add-row">Agregar Fila</button>
                        </div>
                    </div>

                    <!-- Sección: Cálculos y Resultados -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Cálculos y Resultados</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="promedio_muestra">Promedio de la(s) Muestra(s)</label>
                                        <input type="text" class="form-control" id="promedio_muestra" name="promedio_muestra" value="{{ old('promedio_muestra', $displayPhAnalysis->promedio_muestra ?? '') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="observaciones_generales">Observaciones Generales</label>
                                        <textarea class="form-control" id="observaciones_generales" name="observaciones_generales" rows="3">{{ old('observaciones_generales', $displayPhAnalysis->observaciones_generales ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Análisis de pH</button>
                        <a href="{{ route('ph_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to calculate % Error and update Aceptabilidad
        function calculateControlMetrics(rowIndex) {
            const valorLeidoInput = document.querySelector(`input[name='controles_analiticos[${rowIndex}][valor_leido]']`);
            const valorEsperadoInput = document.querySelector(`input[name='controles_analiticos[${rowIndex}][valor_esperado]']`);
            const errorSpan = document.getElementById(`error_${rowIndex}`);
            const aceptabilidadSpan = document.getElementById(`aceptabilidad_${rowIndex}`);

            const valorLeido = parseFloat(valorLeidoInput.value);
            const valorEsperado = parseFloat(valorEsperadoInput.value);

            if (!isNaN(valorLeido) && !isNaN(valorEsperado) && valorEsperado !== 0) {
                const error = ((valorLeido - valorEsperado) / valorEsperado) * 100;
                errorSpan.textContent = error.toFixed(2) + '%';

                const isAcceptable = Math.abs(error) <= 5; // Assuming acceptable error is within 5%
                aceptabilidadSpan.textContent = isAcceptable ? 'Aceptable' : 'No Aceptable';
                aceptabilidadSpan.style.color = isAcceptable ? 'green' : 'red';
            } else {
                errorSpan.textContent = '';
                aceptabilidadSpan.textContent = '';
            }
        }

        // Add event listeners to control inputs
        document.querySelectorAll('.control-valor-leido, .control-valor-esperado').forEach(input => {
            input.addEventListener('input', function() {
                const rowIndex = this.dataset.index;
                calculateControlMetrics(rowIndex);
            });
        });

        // Initial calculation for existing controls
        document.querySelectorAll('.control-row').forEach((row, index) => {
            calculateControlMetrics(index);
        });

        // Function to update average sample value
        function updatePromedioMuestra() {
            let total = 0;
            let count = 0;
            document.querySelectorAll('input[name^="items"][name$="[valor_leido]"]').forEach(input => {
                const value = parseFloat(input.value);
                if (!isNaN(value)) {
                    total += value;
                    count++;
                }
            });
            const promedio = count > 0 ? (total / count).toFixed(2) : '';
            document.getElementById('promedio_muestra').value = promedio;
        }

        // Add row functionality
        document.getElementById('add-row').addEventListener('click', function () {
            const tableBody = document.querySelector('#precision_analitica_table tbody');
            const newRowIndex = tableBody.children.length;
            const newRow = `
                <tr class="item-row">
                    <td><input type="text" class="form-control" name="items[${newRowIndex}][identificacion]" value="" required></td>
                    <td><input type="number" step="0.001" class="form-control" name="items[${newRowIndex}][peso]" value="" required></td>
                    <td><input type="number" step="0.01" class="form-control" name="items[${newRowIndex}][volumen_agua]" value="" required></td>
                    <td><input type="number" step="0.1" class="form-control" name="items[${newRowIndex}][temperatura]" value="" required></td>
                    <td><input type="number" step="0.01" class="form-control" name="items[${newRowIndex}][valor_leido]" value="" required></td>
                    <td><input type="text" class="form-control" name="items[${newRowIndex}][observaciones]" value=""></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);
            // Re-attach event listeners for new row
            tableBody.lastElementChild.querySelector('input[name$="[valor_leido]"]').addEventListener('input', updatePromedioMuestra);
            tableBody.lastElementChild.querySelector('.remove-row').addEventListener('click', function() {
                this.closest('tr').remove();
                updatePromedioMuestra();
            });
        });

        // Remove row functionality
        document.getElementById('precision_analitica_table').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-row')) {
                event.target.closest('tr').remove();
                updatePromedioMuestra();
            }
        });

        // Update promedio_muestra on input changes
        document.querySelectorAll('input[name^="items"][name$="[valor_leido]"]').forEach(input => {
            input.addEventListener('input', updatePromedioMuestra);
        });

        // Initial calculation on page load
        updatePromedioMuestra();

        // Control Aceptability Validation before form submission
        document.getElementById('phAnalysisForm').addEventListener('submit', function (event) {
            let allControlsValid = true;
            let anyControlFilled = false;
            document.querySelectorAll('.control-row').forEach(row => {
                const valorLeidoInput = row.querySelector('.control-valor-leido');
                const valorEsperadoInput = row.querySelector('.control-valor-esperado');
                const aceptabilidadSpan = row.querySelector(`[id^="aceptabilidad_"]`);

                if (valorLeidoInput.value !== '' || valorEsperadoInput.value !== '') {
                    anyControlFilled = true;
                    if (aceptabilidadSpan.textContent !== 'Aceptable') {
                        allControlsValid = false;
                    }
                }
            });

            const controlesErrorDiv = document.getElementById('controles-error');
            if (!anyControlFilled || !allControlsValid) {
                controlesErrorDiv.style.display = 'block';
                event.preventDefault();
            } else {
                controlesErrorDiv.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection