@extends('layouts.app')

@section('title', 'Procesar Análisis de Fósforo')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Fósforo</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('phosphorus_analysis.index') }}">Gestión de Fósforo</a></li>
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
                    {{ session('error') }}\
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Proceso</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID Proceso:</strong> {{ $process->process_id }}</p>
                            <p><strong>Cliente:</strong> {{ $process->quote->customer->nombre }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Solicitud:</strong> {{ $process->created_at->format('d/m/Y') }}</p>
                            <p><strong>Servicio:</strong> {{ $analysis->service->descripcion }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('phosphorus_analysis.store_phosphorus_analysis', [$process->process_id, $analysis->service_id]) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Datos del Análisis</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="consecutivo_no">Número Consecutivo:</label>
                                    <input type="text" class="form-control @error('consecutivo_no') is-invalid @enderror" 
                                           id="consecutivo_no" name="consecutivo_no" value="{{ old('consecutivo_no') }}" required>
                                    @error('consecutivo_no')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="equipo_utilizado">Equipo Utilizado:</label>
                                    <input type="text" class="form-control @error('equipo_utilizado') is-invalid @enderror" 
                                           id="equipo_utilizado" name="equipo_utilizado" value="{{ old('equipo_utilizado') }}" required>
                                    @error('equipo_utilizado')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="resolucion_instrumental">Resolución Instrumental:</label>
                                    <input type="text" class="form-control @error('resolucion_instrumental') is-invalid @enderror" 
                                           id="resolucion_instrumental" name="resolucion_instrumental" value="{{ old('resolucion_instrumental') }}" required>
                                    @error('resolucion_instrumental')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unidades_reporte">Unidades de Reporte:</label>
                                    <input type="text" class="form-control @error('unidades_reporte') is-invalid @enderror" 
                                           id="unidades_reporte" name="unidades_reporte" value="{{ old('unidades_reporte') }}" required>
                                    @error('unidades_reporte')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="intervalo_metodo">Intervalo del Método:</label>
                                    <input type="text" class="form-control @error('intervalo_metodo') is-invalid @enderror" 
                                           id="intervalo_metodo" name="intervalo_metodo" value="{{ old('intervalo_metodo') }}" required>
                                    @error('intervalo_metodo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="items_ensayo">Items de Ensayo:</label>
                                    <textarea class="form-control @error('items_ensayo') is-invalid @enderror" 
                                              id="items_ensayo" name="items_ensayo" rows="3" required>{{ old('items_ensayo') }}</textarea>
                                    @error('items_ensayo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="controles_analiticos">Controles Analíticos:</label>
                                    <textarea class="form-control @error('controles_analiticos') is-invalid @enderror" 
                                              id="controles_analiticos" name="controles_analiticos" rows="3" required>{{ old('controles_analiticos') }}</textarea>
                                    @error('controles_analiticos')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="precision_analitica">Precisión Analítica:</label>
                                    <textarea class="form-control @error('precision_analitica') is-invalid @enderror" 
                                              id="precision_analitica" name="precision_analitica" rows="3" required>{{ old('precision_analitica') }}</textarea>
                                    @error('precision_analitica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="veracidad_analitica">Veracidad Analítica:</label>
                                    <textarea class="form-control @error('veracidad_analitica') is-invalid @enderror" 
                                              id="veracidad_analitica" name="veracidad_analitica" rows="3" required>{{ old('veracidad_analitica') }}</textarea>
                                    @error('veracidad_analitica')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">Observaciones:</label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                              id="observaciones" name="observaciones" rows="3">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4>Parámetros de calibración</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unidad_concentracion">Unidad de concentración:</label>
                                    <input type="text" class="form-control" id="unidad_concentracion" name="unidad_concentracion" value="{{ old('unidad_concentracion') }}">
                                </div>
                                <div class="form-group">
                                    <label for="regresion">Regresión:</label>
                                    <input type="text" class="form-control" id="regresion" name="regresion" value="{{ old('regresion') }}">
                                </div>
                                <div class="form-group">
                                    <label for="longitud_onda">Valor en la longitud de onda:</label>
                                    <input type="text" class="form-control" id="longitud_onda" name="longitud_onda" value="{{ old('longitud_onda') }}">
                                </div>
                                <div class="form-group">
                                    <label for="espesor_capa">Espesor de capa [cm]:</label>
                                    <input type="text" class="form-control" id="espesor_capa" name="espesor_capa" value="{{ old('espesor_capa') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_hora_medida">Fecha y hora de la medida:</label>
                                    <input type="datetime-local" class="form-control" id="fecha_hora_medida" name="fecha_hora_medida" value="{{ old('fecha_hora_medida') }}">
                                </div>
                                <div class="form-group">
                                    <label for="coeficientes_calculados">Coeficiente calculados:</label>
                                    <input type="text" class="form-control" id="coeficientes_calculados" name="coeficientes_calculados" value="{{ old('coeficientes_calculados') }}">
                                </div>
                                <div class="form-group">
                                    <label for="grado_determinacion">Grado de determinación:</label>
                                    <input type="text" class="form-control" id="grado_determinacion" name="grado_determinacion" value="{{ old('grado_determinacion') }}">
                                </div>
                                <div class="form-group">
                                    <label for="valor_limite">Valor límite [mg/L]:</label>
                                    <input type="text" class="form-control" id="valor_limite" name="valor_limite" value="{{ old('valor_limite') }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4>REPORTE DE RESULTADOS</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="reporte_resultados_table">
                                <thead>
                                    <tr>
                                        <th>Código Interno</th>
                                        <th>Peso muestra (g)</th>
                                        <th>pW</th>
                                        <th>Ve (mL)</th>
                                        <th>LBP (mg/L)</th>
                                        <th>Factor de dilución (fd)</th>
                                        <th>Lectura (Abs)</th>
                                        <th>Fósforo disponible (mg/L)</th>
                                        <th>Fósforo disponible (mg/Kg - ppm)</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" name="reporte_resultados[0][codigo_interno]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][peso_muestra]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][pW]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][Ve]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][LBP]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][factor_dilucion]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][lectura_abs]"></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][fosforo_disponible_mgL]" readonly></td>
                                        <td><input type="number" step="any" class="form-control" name="reporte_resultados[0][fosforo_disponible_mgKg]" readonly></td>
                                        <td><input type="text" class="form-control" name="reporte_resultados[0][observaciones_reporte]"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm" id="add_reporte_row">+</button>
                        </div>

                        <hr>
                        <h4>CONTROLES DE CALIDAD (EXACTITUD)</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="controles_calidad_table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Valor esperado (mg/Kg)</th>
                                        <th>Valor leído (mg/Kg)</th>
                                        <th>% de error</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Estándar</td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_esperado]"></td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][valor_leido]"></td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[0][porcentaje_error]" readonly></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                                    </tr>
                                    <tr>
                                        <td>Material de referencia</td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_esperado]"></td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][valor_leido]"></td>
                                        <td><input type="number" step="any" class="form-control" name="controles_calidad[1][porcentaje_error]" readonly></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm" id="add_control_row">+</button>
                        </div>

                        <hr>
                        <h4>Gráfica de Concentración vs. Absorbancia</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <canvas id="phosphorusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                        <a href="{{ route('phosphorus_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let reporteRowIndex = 0;
        $('#add_reporte_row').click(function() {
            reporteRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="reporte_resultados[${reporteRowIndex}][codigo_interno]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][peso_muestra]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][pW]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][Ve]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][LBP]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][factor_dilucion]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][lectura_abs]"></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][fosforo_disponible_mgL]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="reporte_resultados[${reporteRowIndex}][fosforo_disponible_mgKg]" readonly></td>
                    <td><input type="text" class="form-control" name="reporte_resultados[${reporteRowIndex}][observaciones_reporte]"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#reporte_resultados_table tbody').append(newRow);
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        let controlRowIndex = 0;
        $('#add_control_row').click(function() {
            controlRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="controles_calidad[${controlRowIndex}][tipo]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_esperado]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_leido]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_error]" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#controles_calidad_table tbody').append(newRow);
        });

        // Función para recopilar los datos de la tabla y convertirlos a JSON
        function getTableDataAsJson(tableId) {
            let data = [];
            $(`#${tableId} tbody tr`).each(function() {
                let row = {};
                $(this).find('input, select, textarea').each(function() {
                    let name = $(this).attr('name');
                    let matches = name.match(/^(.*?)\\[(\\d+)\\]\\[(.*)\\]$/);
                    if (matches) {
                        let fieldName = matches[3];
                        row[fieldName] = $(this).val();
                    }
                });
                data.push(row);
            });
            return JSON.stringify(data);
        }

        // Calcular Fósforo disponible (mg/L) y Fósforo disponible (mg/Kg - ppm)
        function calculatePhosphorus() {
            const regresionStr = $('#regresion').val();
            const coeficientesStr = $('#coeficientes_calculados').val();
            let m = 0;
            let b = 0;

            console.log('Coeficientes String:', coeficientesStr);

            if (coeficientesStr) {
                const coefMatches = coeficientesStr.match(/m=(-?\d*\.?\d+), b=(-?\d*\.?\d+)/);
                if (coefMatches && coefMatches.length === 3) {
                    m = parseFloat(coefMatches[1]);
                    b = parseFloat(coefMatches[2]);
                    console.log('m extraído:', m, 'b extraído:', b);
                } else {
                    console.warn("Formato de 'Coeficiente calculados' inválido. Asegúrese de usar 'm=X, b=Y'.");
                    console.log('Coeficientes Match:', coefMatches);
                }
            } else {
                console.warn("'Coeficiente calculados' está vacío.");
            }

            $('#reporte_resultados_table tbody tr').each(function() {
                const lecturaAbsInput = $(this).find('input[name$="[lectura_abs]"]');
                const pesoMuestraInput = $(this).find('input[name$="[peso_muestra]"]');
                const factorDilucionInput = $(this).find('input[name$="[factor_dilucion]"]');
                const VeInput = $(this).find('input[name$="[Ve]"]');

                const lecturaAbs = parseFloat(lecturaAbsInput.val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat(pesoMuestraInput.val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat(factorDilucionInput.val().replace(',', '.')) || 0;
                const Ve = parseFloat(VeInput.val().replace(',', '.')) || 0;

                console.log('Fila de Reporte - lecturaAbs:', lecturaAbs, 'pesoMuestra:', pesoMuestra, 'factorDilucion:', factorDilucion, 'Ve:', Ve);

                // Calcular Fósforo disponible (mg/L) -> x = (y - b) / m
                let fosforoMgL = 0;
                if (m !== 0) {
                    fosforoMgL = (lecturaAbs - b) / m;
                } else if (lecturaAbs === b) {
                    fosforoMgL = 0; 
                } else {
                    fosforoMgL = NaN; 
                }
                console.log('Fósforo disponible (mg/L) calculado:', fosforoMgL);
                console.log('Intentando setear fosforo_disponible_mgL con:', fosforoMgL.toFixed(4));
                $(this).find('input[name$="[fosforo_disponible_mgL]"]').val(isNaN(fosforoMgL) ? 'Error' : fosforoMgL.toFixed(4));
                console.log('Valor seteado en fosforo_disponible_mgL:', $(this).find('input[name$="[fosforo_disponible_mgL]"]').val());

                // Calcular Fósforo disponible (mg/Kg - ppm) -> (Fósforo disponible (mg/L) * Ve (mL) * factor de dilución) / Peso muestra (g)
                let fosforoMgKg = 0;
                if (!isNaN(fosforoMgL) && pesoMuestra !== 0) {
                    fosforoMgKg = (fosforoMgL * Ve * factorDilucion) / pesoMuestra;
                } else {
                    fosforoMgKg = NaN; 
                }
                console.log('Fósforo disponible (mg/Kg - ppm) calculado:', fosforoMgKg);
                console.log('Intentando setear fosforo_disponible_mgKg con:', fosforoMgKg.toFixed(4));
                $(this).find('input[name$="[fosforo_disponible_mgKg]"]').val(isNaN(fosforoMgKg) ? 'Error' : fosforoMgKg.toFixed(4));
                console.log('Valor seteado en fosforo_disponible_mgKg:', $(this).find('input[name$="[fosforo_disponible_mgKg]"]').val());
            });
        }

        // Calcular % de error
        function calculateErrorPercentage() {
            $('#controles_calidad_table tbody tr').each(function() {
                const valorEsperadoInput = $(this).find('input[name$="[valor_esperado]"]');
                const valorLeidoInput = $(this).find('input[name$="[valor_leido]"]');

                const valorEsperado = parseFloat(valorEsperadoInput.val().replace(',', '.')) || 0;
                const valorLeido = parseFloat(valorLeidoInput.val().replace(',', '.')) || 0;
                let porcentajeError = 0;
                if (valorEsperado !== 0) {
                    porcentajeError = ((valorLeido - valorEsperado) / valorEsperado) * 100;
                }
                console.log('Intentando setear porcentaje_error con:', porcentajeError.toFixed(2));
                $(this).find('input[name$="[porcentaje_error]"]').val(porcentajeError.toFixed(2));
                console.log('Valor seteado en porcentaje_error:', $(this).find('input[name$="[porcentaje_error]"]').val());
            });
        }

        // Monitorear cambios en los campos relevantes para el cálculo
        $(document).on('input', '#reporte_resultados_table input, #regresion, #coeficientes_calculados', calculatePhosphorus);
        $(document).on('input', '#controles_calidad_table input', calculateErrorPercentage);

        // Inicializar cálculos al cargar la página si hay valores previos
        calculatePhosphorus();
        calculateErrorPercentage();

        // Chart.js initialization
        const ctx = document.getElementById('phosphorusChart').getContext('2d');
        let phosphorusChart;

        function updateChart() {
            if (phosphorusChart) {
                phosphorusChart.destroy();
            }

            const reporteData = [];
            $('#reporte_resultados_table tbody tr').each(function() {
                const concentracion = parseFloat($(this).find('input[name$="[fosforo_disponible_mgL]"]').val().replace(',', '.'));
                const absorbancia = parseFloat($(this).find('input[name$="[lectura_abs]"]').val().replace(',', '.'));
                if (!isNaN(concentracion) && !isNaN(absorbancia)) {
                    reporteData.push({ x: concentracion, y: absorbancia });
                }
            });

            const regresionStr = $('#regresion').val();
            const coeficientesStr = $('#coeficientes_calculados').val();
            let regressionLineData = [];
            let m = 0;
            let b = 0;

            if (coeficientesStr) {
                const coefMatches = coeficientesStr.match(/m=(-?\d*\.?\d+), b=(-?\d*\.?\d+)/);
                if (coefMatches && coefMatches.length === 3) {
                    m = parseFloat(coefMatches[1]);
                    b = parseFloat(coefMatches[2]);
                }
            }

            if (m !== 0 || b !== 0) {
                // Generar puntos para la línea de regresión (ej. de 0 a 15 mg/L)
                for (let x = 0; x <= 15; x += 0.5) {
                    regressionLineData.push({ x: x, y: m * x + b });
                }
            }

            phosphorusChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [
                        {
                            label: 'Reporte de Resultados',
                            data: reporteData,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            pointRadius: 5,
                            pointBackgroundColor: 'rgba(75, 192, 192, 1)'
                        },
                        {
                            label: 'Línea de Regresión',
                            data: regressionLineData,
                            borderColor: '#ff0000',
                            borderWidth: 2,
                            type: 'line',
                            fill: false,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    scales: {
                        x: {
                            type: 'linear',
                            position: 'bottom',
                            title: {
                                display: true,
                                text: 'Concentración [mg/L]'
                            },
                            min: 0,
                            max: 15 // Ajustar según sea necesario
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Absorbancia'
                            },
                            min: 0,
                            max: 0.6 // Ajustar según sea necesario
                        }
                    }
                }
            });
        }

        // Actualizar la gráfica cada vez que se modifican los datos relevantes
        $(document).on('input', '#reporte_resultados_table input, #regresion, #coeficientes_calculados', updateChart);
        
        // Inicializar la gráfica al cargar la página
        updateChart();

        // Antes de enviar el formulario, convertir las tablas a JSON
        $('form').submit(function() {
            // Eliminar los campos individuales de la tabla para evitar duplicados
            $('#reporte_resultados_table input').attr('name', '');
            $('#controles_calidad_table input').attr('name', '');
            
            // Re-adjuntar los campos de datos JSON después de eliminar los originales para evitar problemas de re-envío
            $('<input type="hidden" name="reporte_resultados">').val(getTableDataAsJson('reporte_resultados_table')).appendTo(this);
            $('<input type="hidden" name="controles_calidad">').val(getTableDataAsJson('controles_calidad_table')).appendTo(this);
        });

        // Convertir los campos de texto en arrays JSON
        function convertToJsonArray(field) {
            let value = $(field).val();
            try {
                if (value) {
                    let array = value.split('\n').filter(item => item.trim() !== '');
                    $(field).val(JSON.stringify(array));
                }
            } catch (e) {
                console.error('Error converting to JSON:', e);
            }
        }

        // Convertir los campos antes de enviar el formulario
        $('form').on('submit', function() {
            convertToJsonArray('#items_ensayo');
            convertToJsonArray('#controles_analiticos');
            convertToJsonArray('#precision_analitica');
            convertToJsonArray('#veracidad_analitica');
        });
    });
</script>
@endpush