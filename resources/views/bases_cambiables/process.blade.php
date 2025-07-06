@extends('layouts.app')

@section('contenido')
<div class="content-wrapper">
    <!-- Encabezado -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesar Análisis de Bases Cambiables</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Bases Cambiables</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <!-- Mostrar el campo Proceso arriba del formulario -->
            @if(isset($process))
            <div class="card mb-3">
                <div class="card-body">
                    <strong>Proceso:</strong> {{ $process->process_id ?? $processId }}
                </div>
            </div>
            @endif
            <form method="POST" action="{{ route('bases_cambiables_analysis.store', ['processId' => $processId, 'serviceId' => $serviceId]) }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Método:</label>
                        <input type="text" class="form-control" name="metodo">
                    </div>
                    <div class="col-md-3">
                        <label>Equipo utilizado:</label>
                        <input type="text" class="form-control" name="equipo_utilizado">
                    </div>
                    <div class="col-md-3">
                        <label>Intervalo:</label>
                        <input type="text" class="form-control" name="intervalo">
                    </div>
                    <div class="col-md-3">
                        <label>Nombre Analista:</label>
                        <input type="text" class="form-control" name="nombre_analista">
                    </div>
                </div>

                <!-- Tabla de resultados para K, Ca, Mg -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Resultados de Bases Cambiables</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="overflow-x: auto; min-width: 100%;">
                            <table class="table table-bordered" id="tabla-bases-cambiables" style="min-width: 1800px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th rowspan="2">#</th>
                                        <th rowspan="2">Código interno</th>
                                        <th rowspan="2">Peso muestra (g)</th>
                                        <th rowspan="2">Humedad (%)</th>
                                        <th rowspan="2">Volumen final (mL)</th>
                                        <th colspan="4">Na</th>
                                        <th colspan="4">K</th>
                                        <th colspan="4">Ca</th>
                                        <th colspan="4">Mg</th>
                                        <th rowspan="2">Observaciones</th>
                                        <th rowspan="2"></th>
                                    </tr>
                                    <tr>
                                        <th>Lectura (mg/L)</th>
                                        <th>Blanco</th>
                                        <th>Factor dilución</th>
                                        <th>Resultados cmol(+)/kg</th>
                                        <th>Lectura (mg/L)</th>
                                        <th>Blanco</th>
                                        <th>Factor dilución</th>
                                        <th>Resultados cmol(+)/kg</th>
                                        <th>Lectura (mg/L)</th>
                                        <th>Blanco</th>
                                        <th>Factor dilución</th>
                                        <th>Resultados cmol(+)/kg</th>
                                        <th>Lectura (mg/L)</th>
                                        <th>Blanco</th>
                                        <th>Factor dilución</th>
                                        <th>Resultados cmol(+)/kg</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="fila-muestra">
                                        <td class="numero-fila">1</td>
                                        <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="codigo_interno[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="peso_muestra[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:90px;" name="humedad[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:110px;" name="volumen_final[]"></td>
                                        <!-- Na -->
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="na_lectura[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="na_blanco[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="na_factor[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px;" name="na_resultado[]" readonly></td>
                                        <!-- K -->
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="k_lectura[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="k_blanco[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="k_factor[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px;" name="k_resultado[]" readonly></td>
                                        <!-- Ca -->
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="ca_lectura[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="ca_blanco[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="ca_factor[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px;" name="ca_resultado[]" readonly></td>
                                        <!-- Mg -->
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="mg_lectura[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="mg_blanco[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:100px;" name="mg_factor[]"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg" style="min-width:120px;" name="mg_resultado[]" readonly></td>
                                        <td><input type="text" class="form-control form-control-lg" style="min-width:120px;" name="observaciones[]"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm btn-quitar-fila">-</button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="btn-agregar-fila">Agregar muestra</button>
                        </div>
                    </div>
                </div>

                <!-- Observaciones generales -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Observaciones Generales</strong></div>
                    <div class="card-body">
                        <textarea class="form-control" name="observaciones_generales" rows="3"></textarea>
                    </div>
                </div>

                <!-- Controles de calidad (Exactitud) -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Controles de calidad (Exactitud)</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Material de Referencia</th>
                                        <th>Identificación</th>
                                        <th>Valor esperado mg/kg</th>
                                        <th>Valor leído mg/kg</th>
                                        <th>% Recuperación</th>
                                        <th>Aceptable/no aceptable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Na</td>
                                        <td><input type="text" class="form-control" name="mrc_na_identificacion"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_na_valor_esperado"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_na_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_na_recuperacion" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_na_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_na_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>K</td>
                                        <td><input type="text" class="form-control" name="mrc_k_identificacion"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_k_valor_esperado"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_k_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_k_recuperacion" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_k_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_k_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Ca</td>
                                        <td><input type="text" class="form-control" name="mrc_ca_identificacion"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_ca_valor_esperado"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_ca_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_ca_recuperacion" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_ca_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_ca_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Mg</td>
                                        <td><input type="text" class="form-control" name="mrc_mg_identificacion"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_mg_valor_esperado"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_mg_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="mrc_mg_recuperacion" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_mg_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="mrc_mg_observaciones"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Control de estándar (Exactitud) -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Control de Estándar (Exactitud)</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Estándar</th>
                                        <th>Concentración mg/L</th>
                                        <th>Valor leído mg/L</th>
                                        <th>% Error</th>
                                        <th>Aceptable / No Aceptable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Na</td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_na_concentracion"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_na_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_na_error" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_na_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_na_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>K</td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_k_concentracion"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_k_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_k_error" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_k_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_k_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Ca</td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_ca_concentracion"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_ca_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_ca_error" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_ca_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_ca_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Mg</td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_mg_concentracion"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_mg_valor_leido"></td>
                                        <td><input type="number" step="any" class="form-control" name="estandar_mg_error" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_mg_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="estandar_mg_observaciones"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Curva de calibración -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Curva de Calibración</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Elemento</th>
                                        <th>R² Obtenido</th>
                                        <th>R² Esperado</th>
                                        <th>Aceptable / No Aceptable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Na</td>
                                        <td><input type="number" step="any" class="form-control" name="curva_na_r2_obtenido"></td>
                                        <td><input type="number" step="any" class="form-control" name="curva_na_r2_esperado" value="0.995"></td>
                                        <td><input type="text" class="form-control" name="curva_na_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="curva_na_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>K</td>
                                        <td><input type="number" step="any" class="form-control" name="curva_k_r2_obtenido"></td>
                                        <td><input type="number" step="any" class="form-control" name="curva_k_r2_esperado" value="0.995"></td>
                                        <td><input type="text" class="form-control" name="curva_k_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="curva_k_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Ca</td>
                                        <td><input type="number" step="any" class="form-control" name="curva_ca_r2_obtenido"></td>
                                        <td><input type="number" step="any" class="form-control" name="curva_ca_r2_esperado" value="0.995"></td>
                                        <td><input type="text" class="form-control" name="curva_ca_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="curva_ca_observaciones"></td>
                                    </tr>
                                    <tr>
                                        <td>Mg</td>
                                        <td><input type="number" step="any" class="form-control" name="curva_mg_r2_obtenido"></td>
                                        <td><input type="number" step="any" class="form-control" name="curva_mg_r2_esperado" value="0.995"></td>
                                        <td><input type="text" class="form-control" name="curva_mg_aceptable" readonly></td>
                                        <td><input type="text" class="form-control" name="curva_mg_observaciones"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Blanco del método (versión simple como en el Excel) -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Blanco del método</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Resultado mg/kg</th>
                                        <th>LCM</th>
                                        <th>Aceptable/no aceptable</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $blancos = [
                                            ['id' => 'na', 'nombre' => 'Blanco Na', 'lcm' => 0.2],
                                            ['id' => 'k', 'nombre' => 'Blanco K', 'lcm' => 0.2],
                                            ['id' => 'ca', 'nombre' => 'Blanco Ca', 'lcm' => 0.3],
                                            ['id' => 'mg', 'nombre' => 'Blanco Mg', 'lcm' => 0.2],
                                        ];
                                    @endphp
                                    @foreach($blancos as $b)
                                    <tr>
                                        <td>{{ $b['nombre'] }}</td>
                                        <td><input type="number" step="any" class="form-control form-control-lg blanco-resultado" name="blanco_resultado_{{ $b['id'] }}"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg blanco-lcm" name="blanco_lcm_{{ $b['id'] }}" value="{{ $b['lcm'] }}"></td>
                                        <td><input type="text" class="form-control form-control-lg blanco-aceptable" name="blanco_aceptable_{{ $b['id'] }}" readonly></td>
                                        <td><input type="text" class="form-control form-control-lg" name="blanco_observaciones_{{ $b['id'] }}"></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Duplicado de muestra -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Duplicado de muestra</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Identificación muestra</th>
                                        <th>Replica 1</th>
                                        <th>Replica 2</th>
                                        <th>% DPR</th>
                                        <th>Elemento</th>
                                        <th>Aceptable/no aceptable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $elementos = ['Na', 'K', 'Ca', 'Mg'];
                                    @endphp
                                    @foreach($elementos as $el)
                                    <tr>
                                        <td><input type="text" class="form-control form-control-lg duplicado-id" name="duplicado_id_{{ strtolower($el) }}"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg duplicado-rep1" name="duplicado_rep1_{{ strtolower($el) }}"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg duplicado-rep2" name="duplicado_rep2_{{ strtolower($el) }}"></td>
                                        <td><input type="number" step="any" class="form-control form-control-lg duplicado-dpr" name="duplicado_dpr_{{ strtolower($el) }}" readonly></td>
                                        <td>{{ $el }}</td>
                                        <td><input type="text" class="form-control form-control-lg duplicado-aceptable" name="duplicado_aceptable_{{ strtolower($el) }}" readonly></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Análisis de Bases Cambiables</button>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Agregar fila
    $('#btn-agregar-fila').click(function() {
        var fila = $('#tabla-bases-cambiables tbody tr:first').clone();
        fila.find('input').val('');
        var num = $('#tabla-bases-cambiables tbody tr').length + 1;
        fila.find('.numero-fila').text(num);
        $('#tabla-bases-cambiables tbody').append(fila);
    });
    // Quitar fila
    $(document).on('click', '.btn-quitar-fila', function() {
        if ($('#tabla-bases-cambiables tbody tr').length > 1) {
            $(this).closest('tr').remove();
            // Re-enumerar filas
            $('#tabla-bases-cambiables tbody tr').each(function(i, tr) {
                $(tr).find('.numero-fila').text(i+1);
            });
        }
    });

    // --- Lógica de cálculo automático ---
    function calcularResultados() {
        $('#tabla-bases-cambiables tbody tr').each(function() {
            var fila = $(this);
            var peso_muestra = parseFloat(fila.find('input[name="peso_muestra[]"]').val()) || 0;
            var volumen_final = parseFloat(fila.find('input[name="volumen_final[]"]').val()) || 0;
            var humedad = parseFloat(fila.find('input[name="humedad[]"]').val()) || 0;
            // Na
            var na_lectura = parseFloat(fila.find('input[name="na_lectura[]"]').val()) || 0;
            var na_factor = parseFloat(fila.find('input[name="na_factor[]"]').val()) || 0;
            var na_resultado = '';
            if (peso_muestra > 0 && 23 > 0) {
                na_resultado = (na_lectura * volumen_final * (100 + humedad) * na_factor) / (peso_muestra * 1000 * 23);
                na_resultado = isFinite(na_resultado) ? na_resultado.toFixed(4) : '';
            }
            fila.find('input[name="na_resultado[]"]').val(na_resultado);
            // K
            var k_lectura = parseFloat(fila.find('input[name="k_lectura[]"]').val()) || 0;
            var k_factor = parseFloat(fila.find('input[name="k_factor[]"]').val()) || 0;
            var k_resultado = '';
            if (peso_muestra > 0 && 39 > 0) {
                k_resultado = (k_lectura * volumen_final * (100 + humedad) * k_factor) / (peso_muestra * 1000 * 39);
                k_resultado = isFinite(k_resultado) ? k_resultado.toFixed(4) : '';
            }
            fila.find('input[name="k_resultado[]"]').val(k_resultado);
            // Ca
            var ca_lectura = parseFloat(fila.find('input[name="ca_lectura[]"]').val()) || 0;
            var ca_factor = parseFloat(fila.find('input[name="ca_factor[]"]').val()) || 0;
            var ca_resultado = '';
            if (peso_muestra > 0 && 20 > 0) {
                ca_resultado = (ca_lectura * volumen_final * (100 + humedad) * ca_factor) / (peso_muestra * 1000 * 20);
                ca_resultado = isFinite(ca_resultado) ? ca_resultado.toFixed(4) : '';
            }
            fila.find('input[name="ca_resultado[]"]').val(ca_resultado);
            // Mg
            var mg_lectura = parseFloat(fila.find('input[name="mg_lectura[]"]').val()) || 0;
            var mg_factor = parseFloat(fila.find('input[name="mg_factor[]"]').val()) || 0;
            var mg_resultado = '';
            if (peso_muestra > 0 && 12.16 > 0) {
                mg_resultado = (mg_lectura * volumen_final * (100 + humedad) * mg_factor) / (peso_muestra * 1000 * 12.16);
                mg_resultado = isFinite(mg_resultado) ? mg_resultado.toFixed(4) : '';
            }
            fila.find('input[name="mg_resultado[]"]').val(mg_resultado);
        });
    }

    // Disparar cálculo al cambiar cualquier input relevante
    $(document).on('input', '#tabla-bases-cambiables input', calcularResultados);

    // --- Controles de calidad (Exactitud) ---
    function calcularRecuperacionYAceptabilidad(prefix) {
        var valor_esperado = parseFloat($('input[name="'+prefix+'_valor_esperado"]').val()) || 0;
        var valor_leido = parseFloat($('input[name="'+prefix+'_valor_leido"]').val()) || 0;
        var recuperacion = '';
        var aceptable = '';
        if (valor_esperado > 0) {
            recuperacion = (valor_leido / valor_esperado) * 100;
            recuperacion = isFinite(recuperacion) ? recuperacion.toFixed(2) : '';
            if (recuperacion >= 90 && recuperacion <= 110) {
                aceptable = 'Aceptable';
            } else {
                aceptable = 'No aceptable';
            }
        }
        $('input[name="'+prefix+'_recuperacion"]').val(recuperacion);
        $('input[name="'+prefix+'_aceptable"]').val(aceptable);
    }
    ['mrc_na','mrc_k','mrc_ca','mrc_mg'].forEach(function(prefix) {
        $(document).on('input', 'input[name="'+prefix+'_valor_esperado"], input[name="'+prefix+'_valor_leido"]', function() {
            calcularRecuperacionYAceptabilidad(prefix);
        });
    });

    // --- Control de estándar (Exactitud) ---
    function calcularErrorYEstandar(prefix) {
        var esperado = parseFloat($('input[name="estandar_'+prefix+'_concentracion"]').val()) || 0;
        var leido = parseFloat($('input[name="estandar_'+prefix+'_valor_leido"]').val()) || 0;
        var error = '';
        var aceptable = '';
        if (esperado > 0) {
            error = Math.abs((leido - esperado) / esperado) * 100;
            error = isFinite(error) ? error.toFixed(2) : '';
            if (error >= 0 && error <= 10) {
                aceptable = 'Aceptable';
            } else {
                aceptable = 'No aceptable';
            }
        }
        $('input[name="estandar_'+prefix+'_error"]').val(error);
        $('input[name="estandar_'+prefix+'_aceptable"]').val(aceptable);
    }
    ['na','k','ca','mg'].forEach(function(prefix) {
        $(document).on('input', 'input[name="estandar_'+prefix+'_concentracion"], input[name="estandar_'+prefix+'_valor_leido"]', function() {
            calcularErrorYEstandar(prefix);
        });
    });

    // --- Curva de calibración (R²) ---
    function calcularAceptabilidadCurva(prefix) {
        var r2 = parseFloat($('input[name="curva_'+prefix+'_r2_obtenido"]').val()) || 0;
        var aceptable = '';
        if (r2 >= 0.995) {
            aceptable = 'Aceptable';
        } else if (r2 > 0) {
            aceptable = 'No aceptable';
        }
        $('input[name="curva_'+prefix+'_aceptable"]').val(aceptable);
    }
    ['na','k','ca','mg'].forEach(function(prefix) {
        $(document).on('input', 'input[name="curva_'+prefix+'_r2_obtenido"]', function() {
            calcularAceptabilidadCurva(prefix);
        });
    });

    // Blanco del método (versión simple)
    function calcularBlancos() {
        const blancos = [
            {id: 'na', lcm: 0.2},
            {id: 'k', lcm: 0.2},
            {id: 'ca', lcm: 0.3},
            {id: 'mg', lcm: 0.2}
        ];
        blancos.forEach(function(b) {
            var resultado = parseFloat($('input[name="blanco_resultado_' + b.id + '"]').val()) || 0;
            var lcm = parseFloat($('input[name="blanco_lcm_' + b.id + '"]').val()) || 0;
            var aceptable = (resultado < lcm) ? 'Aceptable' : 'No aceptable';
            $('input[name="blanco_aceptable_' + b.id + '"]').val(aceptable);
        });
    }
    $(document).on('input', '.blanco-resultado', calcularBlancos);
    calcularBlancos();

    // Duplicado de muestra
    function calcularDuplicados() {
        const elementos = ['na','k','ca','mg'];
        elementos.forEach(function(el) {
            var rep1 = parseFloat($('input[name="duplicado_rep1_' + el + '"]').val()) || 0;
            var rep2 = parseFloat($('input[name="duplicado_rep2_' + el + '"]').val()) || 0;
            var dpr = '';
            if ((rep1 + rep2) > 0) {
                dpr = (Math.abs(rep1 - rep2) / ((rep1 + rep2) / 2)) * 100;
                dpr = isFinite(dpr) ? dpr.toFixed(2) : '';
            }
            $('input[name="duplicado_dpr_' + el + '"]').val(dpr);
            var aceptable = (dpr !== '' && dpr <= 20) ? 'Aceptable' : (dpr !== '' ? 'No aceptable' : '');
            $('input[name="duplicado_aceptable_' + el + '"]').val(aceptable);
        });
    }
    $(document).on('input', '.duplicado-rep1, .duplicado-rep2', calcularDuplicados);
    calcularDuplicados();

    // Inicializar cálculos al cargar
    calcularResultados();
    ['mrc_na','mrc_k','mrc_ca','mrc_mg'].forEach(calcularRecuperacionYAceptabilidad);
    ['na','k','ca','mg'].forEach(calcularAceptabilidadCurva);
    ['na','k','ca','mg'].forEach(calcularErrorYEstandar);
});
</script>
@endpush
@endsection 