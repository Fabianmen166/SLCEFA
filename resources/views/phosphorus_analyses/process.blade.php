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
                    <div class="table-responsive mb-4">
    <table class="table table-borderless align-middle" id="datos_analisis_excel" style="background: #f8f9fa; border-radius: 8px;">
        <tr>
            <td class="fw-bold" style="width: 10%">Consecutivo:</td>
            <td style="width: 18%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no') }}"></td>
            <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
            <td colspan="2" style="width: 30%"><span class="form-control-plaintext">Extracción por Bray (II) y cuantificación por ácido ascórbico</span></td>
            <td class="fw-bold" style="width: 10%">Intervalo:</td>
            <td style="width: 16%"><input type="text" class="form-control" name="intervalo_metodo" value="{{ old('intervalo_metodo') }}"></td>
        </tr>
        <tr style="height: 10px;"></tr>
        <tr>
            <td class="fw-bold">Fecha:</td>
            <td><input type="date" class="form-control" name="fecha_analisis" value="{{ old('fecha_analisis') }}"></td>
            <td class="fw-bold">Equipo:</td>
            <td><input type="text" class="form-control" name="equipo_utilizado" value="{{ old('equipo_utilizado') }}"></td>
            <td></td>
            <td class="fw-bold">Analista:</td>
            <td><input type="text" class="form-control" name="analista" value="{{ old('analista') }}"></td>
        </tr>
    </table>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="items_ensayo">Controles analíticos:</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="controles_analiticos_table">
                                            <thead>
                                                <tr>
                                                    <th>Identificación del control analítico</th>
                                                    <th>Valor esperado</th>
                                                    <th>Valor leído</th>
                                                    <th>% de Error</th>
                                                    <th>Aceptabilidad</th>
                                                    <th>% Recuperación</th>
                                                    <th>Aceptabilidad</th>
                                                    <th>% DPR</th>
                                                    <th>Aceptabilidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[0][identificacion]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_esperado]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][valor_leido]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_error]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_error]" readonly></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_recuperacion]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_recuperacion]" readonly></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[0][porcentaje_dpr]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[0][aceptabilidad_dpr]" readonly></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[1][identificacion]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_esperado]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][valor_leido]"></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_error]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_error]" readonly></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_recuperacion]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_recuperacion]" readonly></td>
                                                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[1][porcentaje_dpr]" readonly></td>
                                                    <td><input type="text" class="form-control" name="controles_analiticos[1][aceptabilidad_dpr]" readonly></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                </div>
                                </div>

                                <div class="table-responsive mt-4">
    <table class="table table-bordered" id="curva_duplicados_table">
        <thead>
            <tr>
                <th>Curva de calibración</th>
                <th>Valor</th>
                <th></th>
                <th></th>
                <th></th>
                <th>Duplicado</th>
                <th>Valor leído</th>
                <th>% DPR</th>
                <th>Aceptabilidad</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="2">Curva de calibración</td>
                <td rowspan="2"><input type="number" class="form-control" value="0.995" readonly></td>
                <td colspan="3" rowspan="2"></td>
                <td>Duplicado A</td>
                <td><input type="number" step="any" class="form-control" id="duplicado_a"></td>
                <td rowspan="2"><input type="number" step="any" class="form-control" id="dpr_resultado" readonly></td>
                <td rowspan="2"><input type="text" class="form-control" id="dpr_aceptabilidad" readonly></td>
            </tr>
            <tr>
                <td>Duplicado B</td>
                <td><input type="number" step="any" class="form-control" id="duplicado_b"></td>
            </tr>
        </tbody>
    </table>
                                </div>


                                

                                

                        <hr>
                        <h4>Ítems de ensayo</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="items_ensayo_table">
                                <thead>
                                    <tr>
                                        <th>Código interno</th>
                                        <th>Peso muestra (g)</th>
                                        <th>pW</th>
                                        <th>V. Extractante (mL)</th>
                                        <th>Lectura Blanco (mg/L)</th>
                                        <th>Factor de dilución (fd)</th>
                                        <th>Fósforo disponible (mg/L)</th>
                                        <th>Fósforo disponible (mg/kg)</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" name="items[0][codigo_interno]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][peso_muestra]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][pw]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][v_extractante]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][lectura_blanco]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][factor_dilucion]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][fosforo_disponible_mg_l]"></td>
                                        <td><input type="number" step="any" class="form-control" name="items[0][fosforo_disponible_mg_kg]" readonly></td>
                                        <td><input type="text" class="form-control" name="items[0][observaciones_item]"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm" id="add_item_row">+</button>
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
                    <td><input type="text" class="form-control" name="controles_calidad[${controlRowIndex}][identificacion]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_esperado]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][valor_leido]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_error]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_recuperacion]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_calidad[${controlRowIndex}][porcentaje_dpr]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_calidad[${controlRowIndex}][aceptabilidad]" readonly></td>
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

        // Eliminar scripts de controles de calidad y gráfica
        // Agregar scripts para agregar/eliminar filas de ítems de ensayo
        let itemRowIndex = 0;
        $('#add_item_row').click(function() {
            itemRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="items[${itemRowIndex}][codigo_interno]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][peso_muestra]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][pw]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][v_extractante]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][lectura_blanco]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][factor_dilucion]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][fosforo_disponible_mg_l]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][fosforo_disponible_mg_kg]" readonly></td>
                    <td><input type="text" class="form-control" name="items[${itemRowIndex}][observaciones_item]"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#items_ensayo_table tbody').append(newRow);
        });
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Script para agregar/eliminar filas de controles analíticos
        let controlAnaliticoRowIndex = 0;
        $('#add_control_analitico_row').click(function() {
            controlAnaliticoRowIndex++;
            let newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][identificacion]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][valor_esperado]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][valor_leido]"></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_error]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_error]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_recuperacion]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_recuperacion]" readonly></td>
                    <td><input type="number" step="any" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][porcentaje_dpr]" readonly></td>
                    <td><input type="text" class="form-control" name="controles_analiticos[${controlAnaliticoRowIndex}][aceptabilidad_dpr]" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#controles_analiticos_table tbody').append(newRow);
        });
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Cálculos automáticos para controles analíticos (exactamente dos filas)
        function calcularControlesAnaliticos() {
            // Para cada fila: error, recuperación y aceptabilidad
            for (let i = 0; i < 2; i++) {
                let row = $('#controles_analiticos_table tbody tr').eq(i);
                const valorEsperado = parseFloat(row.find('input[name$="[valor_esperado]"]').val().replace(',', '.')) || 0;
                const valorLeido = parseFloat(row.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
                // % de Error
                let porcentajeError = 0;
                if (valorEsperado !== 0) {
                    porcentajeError = Math.abs((valorLeido - valorEsperado) / valorEsperado) * 100;
                }
                row.find('input[name$="[porcentaje_error]"]').val(porcentajeError.toFixed(2));
                // Aceptabilidad (error)
                let aceptabilidadError = (porcentajeError <= 20) ? 'Aceptable' : 'No aceptable';
                row.find('input[name$="[aceptabilidad_error]"]').val(aceptabilidadError);
                // % Recuperación
                let porcentajeRecuperacion = 0;
                if (valorEsperado !== 0) {
                    porcentajeRecuperacion = (valorLeido / valorEsperado) * 100;
                }
                row.find('input[name$="[porcentaje_recuperacion]"]').val(porcentajeRecuperacion.toFixed(2));
                // Aceptabilidad (recuperación)
                let aceptabilidadRec = (porcentajeRecuperacion >= 80 && porcentajeRecuperacion <= 120) ? 'Aceptable' : 'No aceptable';
                row.find('input[name$="[aceptabilidad_recuperacion]"]').val(aceptabilidadRec);
            }
            // % DPR entre las dos filas
            let row0 = $('#controles_analiticos_table tbody tr').eq(0);
            let row1 = $('#controles_analiticos_table tbody tr').eq(1);
            const valorLeido0 = parseFloat(row0.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
            const valorLeido1 = parseFloat(row1.find('input[name$="[valor_leido]"]').val().replace(',', '.')) || 0;
            let promedio = (valorLeido0 + valorLeido1) / 2;
            let dpr = 0;
            if (promedio !== 0) {
                dpr = Math.abs(valorLeido0 - valorLeido1) / promedio * 100;
            }
            row0.find('input[name$="[porcentaje_dpr]"]').val(dpr.toFixed(2));
            row1.find('input[name$="[porcentaje_dpr]"]').val(dpr.toFixed(2));
            let aceptabilidadDpr = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
            row0.find('input[name$="[aceptabilidad_dpr]"]').val(aceptabilidadDpr);
            row1.find('input[name$="[aceptabilidad_dpr]"]').val(aceptabilidadDpr);
        }
        $(document).on('input', '#controles_analiticos_table input', calcularControlesAnaliticos);
        calcularControlesAnaliticos();

        // Cálculo automático de % DPR y aceptabilidad para duplicados
        function calcularDPR() {
            const a = parseFloat($('#duplicado_a').val().replace(',', '.')) || 0;
            const b = parseFloat($('#duplicado_b').val().replace(',', '.')) || 0;
            let dpr = 0;
            let aceptabilidad = '';
            if ((a + b) !== 0) {
                let promedio = (a + b) / 2;
                dpr = Math.abs(a - b) / promedio * 100;
                aceptabilidad = (dpr <= 20) ? 'Aceptable' : 'No aceptable';
            }
            $('#dpr_resultado').val(dpr.toFixed(2));
            $('#dpr_aceptabilidad').val(aceptabilidad);
        }
        $(document).on('input', '#duplicado_a, #duplicado_b', calcularDPR);
        calcularDPR();

        // Valores fijos de la curva de calibración (ajusta según tu caso)
        const m = 1; // pendiente (ajusta este valor)
        const b = 0; // intersección (ajusta este valor)

        function calcularFosforoEnsayo() {
            $('#items_ensayo_table tbody tr').each(function() {
                const fosforoMgL = parseFloat($(this).find('input[name$="[fosforo_disponible_mg_l]"]').val().replace(',', '.')) || 0;
                const lectura = parseFloat($(this).find('input[name$="[lectura_blanco]"]').val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat($(this).find('input[name$="[peso_muestra]"]').val().replace(',', '.')) || 0;
                const vExtractante = parseFloat($(this).find('input[name$="[v_extractante]"]').val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat($(this).find('input[name$="[factor_dilucion]"]').val().replace(',', '.')) || 0;
                const pw = parseFloat($(this).find('input[name$="[pw]"]').val().replace(',', '.')) || 0;

                // Solo calcular Fósforo disponible (mg/kg) usando el valor ingresado manualmente en mg/L
                let fosforoMgKg = "";
                if (pesoMuestra !== 0) {
                    fosforoMgKg = (((fosforoMgL * vExtractante) - lectura) * factorDilucion / pesoMuestra) * (100 + pw) / 100;
                    $(this).find('input[name$="[fosforo_disponible_mg_kg]"]').val(fosforoMgKg.toFixed(2));
                } else {
                    $(this).find('input[name$="[fosforo_disponible_mg_kg]"]').val('');
                }
            });
        }

        // Ejecutar al cambiar cualquier input relevante
        $(document).on('input', '#items_ensayo_table input', calcularFosforoEnsayo);
        // Ejecutar al cargar la página
        calcularFosforoEnsayo();
    });
</script>
@endpush