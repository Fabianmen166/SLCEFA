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
                    Error: No hay análisis de pH pendientes para procesar.
                </div>
                <a href="{{ route('ph_analysis.index') }}" class="btn btn-secondary">Regresar</a>
            @else
                <!-- Usamos el primer análisis para las secciones generales -->
                @php
                    $firstAnalysis = $pendingAnalyses->first();
                @endphp

                <form action="{{ route('ph_analysis.store') }}" method="POST" id="phAnalysisForm">
                    @csrf

                    <!-- Hidden Input for Analysis IDs -->
                    @foreach ($pendingAnalyses as $analysis)
                        <input type="hidden" name="analyses[{{$analysis->id}}][analysis_id]" value="{{$analysis->id}}">
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
                                        <label for="consecutivo">Consecutivo No.</label>
                                        <input type="text" class="form-control @error('consecutivo_no') is-invalid @enderror" id="consecutivo" name="consecutivo_no" value="{{ old('consecutivo_no', '') }}" required>
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
                                        <input type="date" class="form-control @error('fecha_analisis') is-invalid @enderror" id="fecha_analisis" name="fecha_analisis" value="{{ old('fecha_analisis', $firstAnalysis->phAnalysis->fecha_analisis ?? now()->format('Y-m-d')) }}" required>
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
                                        <input type="text" class="form-control @error('codigo_probeta') is-invalid @enderror" id="codigo_probeta" name="codigo_probeta" value="{{ old('codigo_probeta', $firstAnalysis->phAnalysis->codigo_probeta ?? '') }}" required>
                                        @error('codigo_probeta')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="codigo_equipo">Código del Equipo Potenciométrico</label>
                                        <input type="text" class="form-control @error('codigo_equipo') is-invalid @enderror" id="codigo_equipo" name="codigo_equipo" value="{{ old('codigo_equipo', $firstAnalysis->phAnalysis->codigo_equipo ?? '') }}" required>
                                        @error('codigo_equipo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_electrodo">Serial del Electrodo</label>
                                        <input type="text" class="form-control @error('serial_electrodo') is-invalid @enderror" id="serial_electrodo" name="serial_electrodo" value="{{ old('serial_electrodo', $firstAnalysis->phAnalysis->serial_electrodo ?? '') }}" required>
                                        @error('serial_electrodo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="serial_sonda">Serial de la Sonda de Temperatura</label>
                                        <input type="text" class="form-control @error('serial_sonda_temperatura') is-invalid @enderror" id="serial_sonda" name="serial_sonda_temperatura" value="{{ old('serial_sonda_temperatura', $firstAnalysis->phAnalysis->serial_sonda_temperatura ?? '') }}" required>
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
                                        $controlCount = $pendingAnalyses->count() > 1 ? 4 : 1;
                                    @endphp
                                    @for ($index = 0; $index < $controlCount; $index++)
                                        @php
                                            $identificaciones = ['Buffer de pH 4', 'Buffer de pH 7', 'Buffer de pH 10', 'Muestra de referencia o MRC'];
                                            $identificacion = $identificaciones[$index] ?? $identificaciones[0];
                                        @endphp
                                        <tr class="control-row">
                                            <td><input type="text" class="form-control control-identificacion" name="controles_analiticos[{{$index}}][identificacion]" value="{{ $identificacion }}" readonly></td>
                                            <td><input type="text" class="form-control control-lote @error('controles_analiticos.' . $index . '.lote') is-invalid @enderror" name="controles_analiticos[{{$index}}][lote]" value="{{ old('controles_analiticos.' . $index . '.lote', $firstAnalysis->phAnalysis->controles_analiticos[$index]['lote'] ?? '') }}"></td>
                                            <td><input type="number" step="0.01" class="form-control control-valor-leido @error('controles_analiticos.' . $index . '.valor_leido') is-invalid @enderror" name="controles_analiticos[{{$index}}][valor_leido]" value="{{ old('controles_analiticos.' . $index . '.valor_leido', $firstAnalysis->phAnalysis->controles_analiticos[$index]['valor_leido'] ?? '') }}" data-index="{{$index}}"></td>
                                            <td><input type="number" step="0.01" class="form-control control-valor-esperado @error('controles_analiticos.' . $index . '.valor_esperado') is-invalid @enderror" name="controles_analiticos[{{$index}}][valor_esperado]" value="{{ old('controles_analiticos.' . $index . '.valor_esperado', $firstAnalysis->phAnalysis->controles_analiticos[$index]['valor_esperado'] ?? '') }}" data-index="{{$index}}"></td>
                                            <td><span class="form-control-plaintext" id="error_{{$index}}"></span></td>
                                            <td><span class="form-control-plaintext" id="aceptabilidad_{{$index}}"></span></td>
                                            <td><input type="text" class="form-control @error('controles_analiticos.' . $index . '.observaciones') is-invalid @enderror" name="controles_analiticos[{{$index}}][observaciones]" value="{{ old('controles_analiticos.' . $index . '.observaciones', $firstAnalysis->phAnalysis->controles_analiticos[$index]['observaciones'] ?? '') }}"></td>
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
                            <h3 class="card-title">Precisión Analítica</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Valor Leído (pH)</th>
                                        <th>Promedio</th>
                                        <th>Diferencia</th>
                                        <th>Aceptabilidad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control @error('precision_analitica.duplicado_a.identificacion') is-invalid @enderror" name="precision_analitica[duplicado_a][identificacion]" value="{{ old('precision_analitica.duplicado_a.identificacion', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['identificacion'] ?? '') }}" placeholder="Identificación Duplicado A" required></td>
                                        <td><input type="number" step="0.01" class="form-control duplicado-a @error('precision_analitica.duplicado_a.valor_leido') is-invalid @enderror" name="precision_analitica[duplicado_a][valor_leido]" value="{{ old('precision_analitica.duplicado_a.valor_leido', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['valor_leido'] ?? '') }}" required></td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="promedio"></span></td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="diferencia"></span></td>
                                        <td rowspan="2"><span class="form-control-plaintext" id="aceptabilidad_precision"></span></td>
                                        <td><input type="text" class="form-control @error('precision_analitica.duplicado_a.observaciones') is-invalid @enderror" name="precision_analitica[duplicado_a][observaciones]" value="{{ old('precision_analitica.duplicado_a.observaciones', $firstAnalysis->phAnalysis->precision_analitica['duplicado_a']['observaciones'] ?? '') }}"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="form-control @error('precision_analitica.duplicado_b.identificacion') is-invalid @enderror" name="precision_analitica[duplicado_b][identificacion]" value="{{ old('precision_analitica.duplicado_b.identificacion', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['identificacion'] ?? '') }}" placeholder="Identificación Duplicado B" required></td>
                                        <td><input type="number" step="0.01" class="form-control duplicado-b @error('precision_analitica.duplicado_b.valor_leido') is-invalid @enderror" name="precision_analitica[duplicado_b][valor_leido]" value="{{ old('precision_analitica.duplicado_b.valor_leido', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['valor_leido'] ?? '') }}" required></td>
                                        <td><input type="text" class="form-control @error('precision_analitica.duplicado_b.observaciones') is-invalid @enderror" name="precision_analitica[duplicado_b][observaciones]" value="{{ old('precision_analitica.duplicado_b.observaciones', $firstAnalysis->phAnalysis->precision_analitica['duplicado_b']['observaciones'] ?? '') }}"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sección: Ítems de Ensayo (Unificada) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Ítems de Ensayo Pendientes</h3>
                        </div>
                        <div class="card-body">
                            @if (empty($pendingItems))
                                <p>No hay ítems pendientes para procesar.</p>
                            @else
                                <table class="table table-bordered" id="items_ensayo">
                                    <thead>
                                        <tr>
                                            <th>Código del Proceso</th>
                                            <th>Identificación</th>
                                            <th>Peso (g)</th>
                                            <th>Volumen H₂O (mL)</th>
                                            <th>Temperatura (°C)</th>
                                            <th>Valor Leído (pH)</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingItems as $index => $item)
                                            @php
                                                $analysis = $pendingAnalyses->firstWhere('id', $item['analysis_id']);
                                            @endphp
                                            @if ($analysis)
                                                <tr>
                                                    <td>{{ $analysis->process->process_id }}</td>
                                                    <td><input type="text" class="form-control @error('items_ensayo.' . $index . '.identificacion') is-invalid @enderror" name="items_ensayo[{{$index}}][identificacion]" value="{{ old('items_ensayo.' . $index . '.identificacion', $item['identificacion'] ?? '') }}" required></td>
                                                    <td><input type="number" step="0.01" class="form-control @error('items_ensayo.' . $index . '.peso') is-invalid @enderror" name="items_ensayo[{{$index}}][peso]" value="{{ old('items_ensayo.' . $index . '.peso', $item['peso'] ?? '') }}" required></td>
                                                    <td><input type="number" step="0.01" class="form-control @error('items_ensayo.' . $index . '.volumen_agua') is-invalid @enderror" name="items_ensayo[{{$index}}][volumen_agua]" value="{{ old('items_ensayo.' . $index . '.volumen_agua', $item['volumen_agua'] ?? '') }}" required></td>
                                                    <td><input type="number" step="0.01" class="form-control @error('items_ensayo.' . $index . '.temperatura') is-invalid @enderror" name="items_ensayo[{{$index}}][temperatura]" value="{{ old('items_ensayo.' . $index . '.temperatura', $item['temperatura'] ?? '') }}" required></td>
                                                    <td><input type="number" step="0.01" class="form-control @error('items_ensayo.' . $index . '.valor_leido') is-invalid @enderror" name="items_ensayo[{{$index}}][valor_leido]" value="{{ old('items_ensayo.' . $index . '.valor_leido', $item['valor_leido'] ?? '') }}" required></td>
                                                    <td><input type="text" class="form-control @error('items_ensayo.' . $index . '.observaciones') is-invalid @enderror" name="items_ensayo[{{$index}}][observaciones]" value="{{ old('items_ensayo.' . $index . '.observaciones', $item['observaciones'] ?? '') }}"></td>
                                                    <input type="hidden" name="items_ensayo[{{$index}}][analysis_id]" value="{{$item['analysis_id']}}">
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-primary" id="add-item">Agregar Ítem</button>
                            @endif
                        </div>
                    </div>

                    <!-- Sección: Observaciones -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Observaciones</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" name="observaciones" rows="3">{{ old('observaciones', $firstAnalysis->phAnalysis->observaciones ?? '') }}</textarea>
                                @error('observaciones')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Guardar Análisis de pH</button>
                    <a href="{{ route('ph_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            @endif
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Calcular % Error y Aceptabilidad para Controles Analíticos
    const updateControl = (index) => {
        const valorLeido = parseFloat(document.querySelector(`.control-valor-leido[data-index="${index}"]`).value) || 0;
        const valorEsperado = parseFloat(document.querySelector(`.control-valor-esperado[data-index="${index}"]`).value) || 0;
        if (valorEsperado !== 0) {
            const error = Math.abs((valorLeido - valorEsperado) / valorEsperado) * 100;
            document.getElementById(`error_${index}`).textContent = error.toFixed(2) + '%';
            const aceptabilidad = error <= 5 ? 'Aceptable' : 'No aceptable';
            document.getElementById(`aceptabilidad_${index}`).textContent = aceptabilidad;
            if (aceptabilidad === 'No aceptable') {
                document.getElementById(`aceptabilidad_${index}`).classList.add('text-danger');
            } else {
                document.getElementById(`aceptabilidad_${index}`).classList.remove('text-danger');
            }
        } else {
            document.getElementById(`error_${index}`).textContent = '';
            document.getElementById(`aceptabilidad_${index}`).textContent = '';
            document.getElementById(`aceptabilidad_${index}`).classList.remove('text-danger');
        }
    };

    document.querySelectorAll('.control-valor-leido').forEach(input => {
        input.addEventListener('input', () => updateControl(input.dataset.index));
    });
    document.querySelectorAll('.control-valor-esperado').forEach(input => {
        input.addEventListener('input', () => updateControl(input.dataset.index));
    });

    // Validar al enviar el formulario
    document.getElementById('phAnalysisForm').addEventListener('submit', function (e) {
        const controlRows = document.querySelectorAll('.control-row');
        let hasValidControl = false;
        let allAcceptable = true;

        controlRows.forEach((row, index) => {
            const lote = row.querySelector('.control-lote').value.trim();
            const valorLeido = row.querySelector('.control-valor-leido').value.trim();
            const valorEsperado = row.querySelector('.control-valor-esperado').value.trim();
            const aceptabilidad = document.getElementById(`aceptabilidad_${index}`).textContent;

            // Verificar si el control está completo
            const isComplete = lote !== '' && valorLeido !== '' && valorEsperado !== '';
            if (isComplete) {
                hasValidControl = true;
                // Verificar aceptabilidad solo para controles completos
                if (aceptabilidad === 'No aceptable') {
                    allAcceptable = false;
                }
            }
        });

        const errorDiv = document.getElementById('controles-error');
        if (!hasValidControl || !allAcceptable) {
            e.preventDefault();
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    });

    // Calcular Precisión Analítica
    const updatePrecision = () => {
        const duplicadoA = parseFloat(document.querySelector('.duplicado-a').value);
        const duplicadoB = parseFloat(document.querySelector('.duplicado-b').value);
        if (!isNaN(duplicadoA) && !isNaN(duplicadoB)) {
            const promedio = (duplicadoA + duplicadoB) / 2;
            const diferencia = Math.abs(duplicadoA - duplicadoB);
            document.getElementById('promedio').textContent = promedio.toFixed(2);
            document.getElementById('diferencia').textContent = diferencia.toFixed(2);
            const aceptabilidad = diferencia <= 0.5 ? 'Aceptable' : 'No aceptable';
            document.getElementById('aceptabilidad_precision').textContent = aceptabilidad;
            if (aceptabilidad === 'No aceptable') {
                document.getElementById('aceptabilidad_precision').classList.add('text-danger');
            } else {
                document.getElementById('aceptabilidad_precision').classList.remove('text-danger');
            }
        } else {
            document.getElementById('promedio').textContent = '';
            document.getElementById('diferencia').textContent = '';
            document.getElementById('aceptabilidad_precision').textContent = '';
            document.getElementById('aceptabilidad_precision').classList.remove('text-danger');
        }
    };

    document.querySelectorAll('.duplicado-a, .duplicado-b').forEach(input => {
        input.addEventListener('input', updatePrecision);
    });

    // Agregar Ítems de Ensayo
    let itemIndex = {{ count($pendingItems) }};
    document.getElementById('add-item').addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="items_ensayo[${itemIndex}][process_id]" placeholder="Ingrese Código del Proceso"></td>
            <td><input type="text" class="form-control" name="items_ensayo[${itemIndex}][identificacion]" value="" placeholder="Ingrese Identificación" required></td>
            <td><input type="number" step="0.01" class="form-control" name="items_ensayo[${itemIndex}][peso]"></td>
            <td><input type="number" step="0.01" class="form-control" name="items_ensayo[${itemIndex}][volumen_agua]"></td>
            <td><input type="number" step="0.01" class="form-control" name="items_ensayo[${itemIndex}][temperatura]"></td>
            <td><input type="number" step="0.01" class="form-control" name="items_ensayo[${itemIndex}][valor_leido]"></td>
            <td><input type="text" class="form-control" name="items_ensayo[${itemIndex}][observaciones]"></td>
            <input type="hidden" name="items_ensayo[${itemIndex}][analysis_id]" value="">
        `;
        document.querySelector('#items_ensayo tbody').appendChild(newRow);
        itemIndex++;
    });
});
</script>
@endsection