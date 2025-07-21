@extends('layouts.app')

@section('title', 'Procesar Análisis de Azufre')

@section('contenido')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Azufre</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sulfur_analysis.index') }}">Gestión de Azufre</a></li>
                        <li class="breadcrumb-item active">Procesar Análisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
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
            <form action="{{ route('sulfur_analysis.store_sulfur_analysis', [$process->process_id, $analysis->service_id]) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Datos del Análisis</h3>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-borderless align-middle" style="background: #f8f9fa; border-radius: 8px;">
                            <tr>
                                <td class="fw-bold" style="width: 10%">Consecutivo:</td>
                                <td style="width: 18%"><input type="text" class="form-control" name="consecutivo_no" value="{{ old('consecutivo_no') }}"></td>
                                <td class="fw-bold" style="width: 16%">Metodología aplicada:</td>
                                <td colspan="2" style="width: 30%"><span class="form-control-plaintext">Extracción y cuantificación colorimétrica</span></td>
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
                </div>
                <!-- Agregado: Controles Analíticos y Curva de Calibración -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Controles Analíticos</h3>
                    </div>
                    <div class="card-body">
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ítems de Ensayo</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            Complete los valores para los ítems de ensayo. El cálculo de Azufre disponible (mg/kg) es automático.
                        </div>
                        <table class="table table-bordered" id="items_ensayo_table">
                            <thead>
                                <tr>
                                    <th>Código interno</th>
                                    <th>Peso muestra (g)</th>
                                    <th>pW</th>
                                    <th>V. Extractante (mL)</th>
                                    <th>Lectura blanco</th>
                                    <th>Factor de dilución (fd)</th>
                                    <th>Azufre disponible (mg/L)</th>
                                    <th>Azufre disponible (mg/kg)</th>
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
                                    <td><input type="number" step="any" class="form-control" name="items[0][sulfur_disponible_mg_l]"></td>
                                    <td><input type="number" step="any" class="form-control" name="items[0][sulfur_disponible_mg_kg]" readonly></td>
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
                    <a href="{{ route('sulfur_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
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
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][sulfur_disponible_mg_l]"></td>
                    <td><input type="number" step="any" class="form-control" name="items[${itemRowIndex}][sulfur_disponible_mg_kg]" readonly></td>
                    <td><input type="text" class="form-control" name="items[${itemRowIndex}][observaciones_item]"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                </tr>
            `;
            $('#items_ensayo_table tbody').append(newRow);
        });
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Cálculo automático de Azufre disponible (mg/kg)
        function calcularAzufreEnsayo() {
            $('#items_ensayo_table tbody tr').each(function() {
                const sulfurMgL = parseFloat($(this).find('input[name$="[sulfur_disponible_mg_l]"]').val().replace(',', '.')) || 0;
                const pesoMuestra = parseFloat($(this).find('input[name$="[peso_muestra]"]').val().replace(',', '.')) || 0;
                const vExtractante = parseFloat($(this).find('input[name$="[v_extractante]"]').val().replace(',', '.')) || 0;
                const factorDilucion = parseFloat($(this).find('input[name$="[factor_dilucion]"]').val().replace(',', '.')) || 0;
                const pw = parseFloat($(this).find('input[name$="[pw]"]').val().replace(',', '.')) || 0;
                let sulfurMgKg = "";
                if (sulfurMgL === 0) {
                    sulfurMgKg = "";
                } else {
                    sulfurMgKg = (sulfurMgL * vExtractante * factorDilucion) / pesoMuestra * (100 + pw) / 100;
                }
                $(this).find('input[name$="[sulfur_disponible_mg_kg]"]').val(sulfurMgKg === "" ? '' : sulfurMgKg.toFixed(2));
            });
        }
        $(document).on('input', '#items_ensayo_table input', calcularAzufreEnsayo);
        calcularAzufreEnsayo();

        // Cálculos automáticos para controles analíticos (exactamente dos filas)
        function calcularControlesAnaliticos() {
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

        // Cálculo automático de % DPR y aceptabilidad para duplicados (curva)
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
    });
</script>
@endpush 