@extends('layouts.app')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Análisis de Intercambio Catiónico</h1>
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos del Análisis</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('cation_exchange_analysis.store_cation_exchange_analysis', ['processId' => $process->process_id, 'serviceId' => $service->services_id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="analysis_ids[]" value="{{ $analysis->id }}">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="consecutivo_no">Consecutivo No.</label>
                                    <input type="text" class="form-control" id="consecutivo_no" name="consecutivo_no" value="{{ old('consecutivo_no', $analysis->consecutivo_no ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_analisis">Fecha de Análisis</label>
                                    <input type="date" class="form-control" id="fecha_analisis" name="fecha_analisis" value="{{ old('fecha_analisis', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="unidades_reporte_equipo">Unidades de reporte equipo</label>
                                    <input type="text" class="form-control" id="unidades_reporte_equipo" name="unidades_reporte_equipo" value="{{ old('unidades_reporte_equipo', $analysis->unidades_reporte_equipo ?? '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_metodo">Nombre del Método</label>
                                    <input type="text" class="form-control" id="nombre_metodo" name="nombre_metodo" value="{{ old('nombre_metodo', $analysis->nombre_metodo ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="equipo_utilizado">Equipo utilizado</label>
                                    <input type="text" class="form-control" id="equipo_utilizado" name="equipo_utilizado" value="{{ old('equipo_utilizado', $analysis->equipo_utilizado ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="intervalo_metodo">Intervalo del método</label>
                                    <input type="text" class="form-control" id="intervalo_metodo" name="intervalo_metodo" value="{{ old('intervalo_metodo', $analysis->intervalo_metodo ?? '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre_analista">Nombre Analista</label>
                                    <input type="text" class="form-control" id="nombre_analista" name="nombre_analista" value="{{ old('nombre_analista', $analysis->nombre_analista ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="resolucion_instrumental">Resolución instrumental</label>
                                    <input type="text" class="form-control" id="resolucion_instrumental" name="resolucion_instrumental" value="{{ old('resolucion_instrumental', $analysis->resolucion_instrumental ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="normalidad_naoh">Normalidad NaOH</label>
                                    <input type="number" step="0.0001" class="form-control" id="normalidad_naoh" name="normalidad_naoh" value="{{ old('normalidad_naoh', $analysis->normalidad_naoh ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Servicio</label>
                                    <p class="form-control-static">{{ $service->descripcion ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Controles Analíticos</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Blanco del Proceso</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="blanco_valor_leido" placeholder="Valor leído (mg/L)" value="{{ old('blanco_valor_leido', $analysis->controles_analiticos['blanco_valor_leido'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="blanco_observaciones" placeholder="Observaciones" value="{{ old('blanco_observaciones', $analysis->controles_analiticos['blanco_observaciones'] ?? '') }}">
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <input type="text" class="form-control" id="blanco_estado" name="blanco_estado" placeholder="Estado" value="{{ old('blanco_estado', $analysis->controles_analiticos['blanco_estado'] ?? '') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Duplicados <span style="font-weight:normal;color:#000;">(Precisión: RPD &lt; 25%)</span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="duplicado_a_valor_leido" placeholder="Duplicado A (mg/L)" value="{{ old('duplicado_a_valor_leido', $analysis->precision_analitica['duplicado_a_valor_leido'] ?? '') }}" required>
                                            <script>console.log('Valor old() para duplicado_a_valor_leido:', '{{ old('duplicado_a_valor_leido') }}');</script>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="duplicado_b_valor_leido" placeholder="Duplicado B (mg/L)" value="{{ old('duplicado_b_valor_leido', $analysis->precision_analitica['duplicado_b_valor_leido'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="duplicado_observaciones" placeholder="Observaciones" value="{{ old('duplicado_observaciones', $analysis->precision_analitica['duplicado_observaciones'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Veracidad</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="number" step="0.0001" class="form-control" name="veracidad_valor_esperado" placeholder="Valor esperado (mg/L)" value="{{ old('veracidad_valor_esperado', $analysis->veracidad_analitica['valor_esperado'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" step="0.0001" class="form-control" name="veracidad_valor_leido" placeholder="Valor leído (mg/L)" value="{{ old('veracidad_valor_leido', $analysis->veracidad_analitica['valor_leido'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="veracidad_recuperacion" placeholder="Recuperación (%)" value="{{ old('veracidad_recuperacion', $analysis->veracidad_analitica['recuperacion'] ?? '') }}" readonly>
                                            <small class="form-text" style="color:#000;">% Recuperación</small>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="veracidad_observaciones" placeholder="Observaciones" value="{{ old('veracidad_observaciones', $analysis->veracidad_analitica['veracidad_observaciones'] ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Controles Analíticos</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Identificación</th>
                                                <th>Peso (g)</th>
                                                <th>Vol NaOH Muestra (ml)</th>
                                                <th>Vol NaOH Blanco (ml)</th>
                                                <th>Humedad (%)</th>
                                                <th>Valor Leído (CIC) <span style="font-weight:normal;color:#000;">cmol(+)/kg</span></th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingItems as $index => $item)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="items_ensayo[{{ $index }}][identificacion]" value="{{ old('items_ensayo.' . $index . '.identificacion', $item['identificacion'] ?? '') }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" class="form-control" name="items_ensayo[{{ $index }}][peso]" value="{{ old('items_ensayo.' . $index . '.peso', $item['peso'] ?? '') }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" class="form-control" name="items_ensayo[{{ $index }}][vol_naoh_muestra]" value="{{ old('items_ensayo.' . $index . '.vol_naoh_muestra', $item['vol_naoh_muestra'] ?? '') }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" class="form-control" name="items_ensayo[{{ $index }}][vol_naoh_blanco]" value="{{ old('items_ensayo.' . $index . '.vol_naoh_blanco', $item['vol_naoh_blanco'] ?? '') }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control" name="items_ensayo[{{ $index }}][humedad]" value="{{ old('items_ensayo.' . $index . '.humedad', $item['humedad'] ?? '') }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.0001" class="form-control" name="items_ensayo[{{ $index }}][valor_leido]" value="{{ old('items_ensayo.' . $index . '.valor_leido', $item['valor_leido'] ?? '') }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="items_ensayo[{{ $index }}][observaciones]" value="{{ old('items_ensayo.' . $index . '.observaciones', $item['observaciones'] ?? '') }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Muestra Referencia Certificada</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Muestra Referencia Certificada</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="muestra_referencia_certificada_valor_teorico" placeholder="Valor teórico (cmol+/kg)" value="{{ old('muestra_referencia_certificada_valor_teorico', $analysis->muestra_referencia_certificada_analitica['valor_teorico'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="muestra_referencia_certificada_valor_leido" placeholder="Valor leído (cmol+/kg)" value="{{ old('muestra_referencia_certificada_valor_leido', $analysis->muestra_referencia_certificada_analitica['valor_leido'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="muestra_referencia_certificada_error_porcentaje" placeholder="%ERROR" value="{{ old('muestra_referencia_certificada_error_porcentaje', $analysis->muestra_referencia_certificada_analitica['error_porcentaje'] ?? '') }}" readonly>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <input type="text" class="form-control" name="muestra_referencia_certificada_estado" placeholder="Estado" value="{{ old('muestra_referencia_certificada_estado', $analysis->muestra_referencia_certificada_analitica['estado'] ?? '') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Muestra Referencia</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Muestra Referencia</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="muestra_referencia_valor_teorico" placeholder="Valor teórico (cmol+/kg)" value="{{ old('muestra_referencia_valor_teorico', $analysis->veracidad_analitica['mr_valor_teorico'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="muestra_referencia_valor_leido" placeholder="Valor leído (cmol+/kg)" value="{{ old('muestra_referencia_valor_leido', $analysis->veracidad_analitica['mr_valor_leido'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="muestra_referencia_recuperacion_porcentaje" placeholder="%REC" value="{{ old('muestra_referencia_recuperacion_porcentaje', $analysis->veracidad_analitica['mr_recuperacion_porcentaje'] ?? '') }}" readonly>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <input type="text" class="form-control" name="muestra_referencia_estado" placeholder="Estado" value="{{ old('muestra_referencia_estado', $analysis->veracidad_analitica['mr_estado'] ?? '') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Diferencia Porcentual Relativa (DPR)</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Duplicados (DPR)</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="dpr_replica_1" placeholder="Resultado réplica 1 (cmol+/kg)" value="{{ old('dpr_replica_1', $analysis->precision_analitica['dpr_replica_1'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" step="0.0001" class="form-control" name="dpr_replica_2" placeholder="Resultado réplica 2 (cmol+/kg)" value="{{ old('dpr_replica_2', $analysis->precision_analitica['dpr_replica_2'] ?? '') }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="dpr_rpd_porcentaje" placeholder="%RPD" value="{{ old('dpr_rpd_porcentaje', $analysis->precision_analitica['dpr_rpd_porcentaje'] ?? '') }}" readonly>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <input type="text" class="form-control" name="dpr_estado" placeholder="Estado" value="{{ old('dpr_estado', $analysis->precision_analitica['dpr_estado'] ?? '') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label for="observaciones">Observaciones Generales</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $analysis->observaciones ?? '') }}</textarea>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                            <a href="{{ route('cation_exchange_analysis.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Script de Análisis de Intercambio Catiónico cargado.');
        // Validación para evitar comas como separador decimal
        $('input[type="number"]').on('input', function(e) {
            let valor = $(this).val();
            if (valor.includes(',')) {
                alert('Por favor, utiliza punto (.) como separador decimal, no coma (,).');
                $(this).val(valor.replace(/,/g, '.'));
                $(this).focus();
            }
        });
        // Prevención al enviar el formulario
        $('form').on('submit', function(e) {
            let hayComa = false;
            $(this).find('input[type="number"]').each(function() {
                if ($(this).val().includes(',')) {
                    hayComa = true;
                    $(this).focus();
                    return false;
                }
            });
            if (hayComa) {
                alert('No se permite el uso de comas como separador decimal. Por favor, usa punto (.)');
                e.preventDefault();
            }
        });
        // Cálculo automático de recuperación en veracidad
        $('input[name="veracidad_valor_leido"], input[name="veracidad_valor_esperado"]').on('input', function() {
            let esperado = parseFloat($('input[name="veracidad_valor_esperado"]').val());
            let leido = parseFloat($('input[name="veracidad_valor_leido"]').val());
            if (!isNaN(esperado) && !isNaN(leido) && esperado > 0) {
                let recuperacion = (leido / esperado) * 100;
                $('input[name="veracidad_recuperacion"]').val(recuperacion.toFixed(2) + '%');
            } else {
                $('input[name="veracidad_recuperacion"]').val('');
            }
        });
        // Cálculo automático de diferencia de duplicados y validación
        $('input[name="duplicado_a_valor_leido"], input[name="duplicado_b_valor_leido"]').on('input', function() {
            let a = parseFloat($('input[name="duplicado_a_valor_leido"]').val());
            let b = parseFloat($('input[name="duplicado_b_valor_leido"]').val());
            if (!isNaN(a) && !isNaN(b)) {
                let diferencia = Math.abs(a - b);
                let promedio = (a + b) / 2;
                let porcentaje = promedio > 0 ? (diferencia / promedio) * 100 : 0;
                let mensaje = 'Diferencia: ' + diferencia.toFixed(4) + ' (' + porcentaje.toFixed(2) + '%)';
                if (porcentaje > 10) {
                    mensaje += ' - No aceptable (>10%)';
                } else {
                    mensaje += ' - Aceptable';
                }
                if ($('input[name="duplicado_observaciones"]').length) {
                    $('input[name="duplicado_observaciones"]').val(mensaje);
                }
            } else {
                $('input[name="duplicado_observaciones"]').val('');
            }
        });

        // Función para calcular CIC
        function calcularCIC(row) {
            let peso = parseFloat(row.find('input[name$="[peso]"]').val());
            let volNaohMuestra = parseFloat(row.find('input[name$="[vol_naoh_muestra]"]').val());
            let volNaohBlanco = parseFloat(row.find('input[name$="[vol_naoh_blanco]"]').val());
            let humedad = parseFloat(row.find('input[name$="[humedad]"]').val());
            let normalidadNaoh = parseFloat($('#normalidad_naoh').val());

            if (!isNaN(peso) && !isNaN(volNaohMuestra) && !isNaN(volNaohBlanco) && !isNaN(humedad) && !isNaN(normalidadNaoh) && peso > 0) {
                let denominador = peso * (1 - (humedad / 100));
                if (denominador === 0) {
                    row.find('input[name$="[valor_leido]"]').val('Error: División por cero');
                    return;
                }
                let cic = ((volNaohMuestra - volNaohBlanco) * normalidadNaoh * 100) / denominador;
                row.find('input[name$="[valor_leido]"]').val(cic.toFixed(4));
            } else {
                row.find('input[name$="[valor_leido]"]').val('');
            }
        }

        // Eventos para calcular CIC
        $('#normalidad_naoh').on('input', function() {
            $('table.table-bordered tbody tr').each(function() {
                calcularCIC($(this));
            });
        });

        $('table.table-bordered tbody').on('input', 'input[name$="[peso]"], input[name$="[vol_naoh_muestra]"], input[name$="[vol_naoh_blanco]"], input[name$="[humedad]"]', function() {
            let row = $(this).closest('tr');
            calcularCIC(row);
        });

        // Cálculo automático de %ERROR en muestra referencia certificada
        $('input[name="muestra_referencia_certificada_valor_teorico"], input[name="muestra_referencia_certificada_valor_leido"]').on('input', function() {
            let valorTeorico = parseFloat($('input[name="muestra_referencia_certificada_valor_teorico"]').val());
            let valorLeido = parseFloat($('input[name="muestra_referencia_certificada_valor_leido"]').val());
            let errorPorcentajeInput = $('input[name="muestra_referencia_certificada_error_porcentaje"]');
            let estadoInput = $('input[name="muestra_referencia_certificada_estado"]');

            if (!isNaN(valorTeorico) && !isNaN(valorLeido) && valorTeorico !== 0) {
                let error = (Math.abs(valorLeido - valorTeorico) / valorTeorico) * 100;
                errorPorcentajeInput.val(error.toFixed(2) + '%');

                if (error <= 10) {
                    estadoInput.val('Aceptable');
                    estadoInput.removeClass('bg-danger').addClass('bg-success');
                } else {
                    estadoInput.val('No Aceptable');
                    estadoInput.removeClass('bg-success').addClass('bg-danger');
                }
            } else {
                errorPorcentajeInput.val('');
                estadoInput.val('');
                estadoInput.removeClass('bg-success bg-danger');
            }
        });

        // Cálculo automático de %REC en muestra referencia
        $('input[name="muestra_referencia_valor_teorico"], input[name="muestra_referencia_valor_leido"]').on('input', function() {
            let valorTeorico = parseFloat($('input[name="muestra_referencia_valor_teorico"]').val());
            let valorLeido = parseFloat($('input[name="muestra_referencia_valor_leido"]').val());
            let recuperacionPorcentajeInput = $('input[name="muestra_referencia_recuperacion_porcentaje"]');
            let estadoInput = $('input[name="muestra_referencia_estado"]');

            if (!isNaN(valorTeorico) && !isNaN(valorLeido) && valorTeorico !== 0) {
                let recuperacion = (valorLeido / valorTeorico) * 100;
                recuperacionPorcentajeInput.val(recuperacion.toFixed(2) + '%');

                if (recuperacion >= 90 && recuperacion <= 110) {
                    estadoInput.val('Aceptable');
                    estadoInput.removeClass('bg-danger').addClass('bg-success');
                } else {
                    estadoInput.val('No Aceptable');
                    estadoInput.removeClass('bg-success').addClass('bg-danger');
                }
            } else {
                recuperacionPorcentajeInput.val('');
                estadoInput.val('');
                estadoInput.removeClass('bg-success bg-danger');
            }
        });

        // Cálculo automático de %RPD en DPR
        $('input[name="dpr_replica_1"], input[name="dpr_replica_2"]').on('input', function() {
            let replica1 = parseFloat($('input[name="dpr_replica_1"]').val());
            let replica2 = parseFloat($('input[name="dpr_replica_2"]').val());
            let rpdPorcentajeInput = $('input[name="dpr_rpd_porcentaje"]');
            let estadoInput = $('input[name="dpr_estado"]');

            if (!isNaN(replica1) && !isNaN(replica2)) {
                let promedio = (replica1 + replica2) / 2;
                if (promedio === 0) {
                    rpdPorcentajeInput.val('Error: División por cero');
                    estadoInput.val('');
                    estadoInput.removeClass('bg-success bg-danger');
                    return;
                }
                let rpd = (Math.abs(replica1 - replica2) / promedio) * 100;
                rpdPorcentajeInput.val(rpd.toFixed(2) + '%');

                if (rpd <= 20) {
                    estadoInput.val('Aceptable');
                    estadoInput.removeClass('bg-danger').addClass('bg-success');
                } else {
                    estadoInput.val('No Aceptable');
                    estadoInput.removeClass('bg-success').addClass('bg-danger');
                }
            } else {
                rpdPorcentajeInput.val('');
                estadoInput.val('');
                estadoInput.removeClass('bg-success bg-danger');
            }
        });

        // Estado del blanco del proceso
        function actualizarEstadoBlanco() {
            let valor = parseFloat($('input[name="blanco_valor_leido"]').val().replace(',', '.'));
            let estado = '';
            if (!isNaN(valor)) {
                estado = (valor <= 1) ? 'Aceptable' : 'No Aceptable';
            }
            $('#blanco_estado').val(estado);
            if(estado === 'No Aceptable') {
                $('#blanco_estado').addClass('bg-danger text-white');
            } else {
                $('#blanco_estado').removeClass('bg-danger text-white');
            }
        }
        $('input[name="blanco_valor_leido"]').on('input', actualizarEstadoBlanco);
        actualizarEstadoBlanco();

        // Bloquear la tecla coma en los campos numéricos
        $('input[type="number"]').on('keydown', function(e) {
            if (e.key === ',') {
                alert('No se permite el uso de comas como separador decimal. Usa punto (.)');
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection 