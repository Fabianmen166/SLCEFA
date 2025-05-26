@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Registro de Análisis de Conductividad (Personal Técnico)</h1>
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
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Formulario de Análisis de Conductividad</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('conductivity.store') }}" method="POST">
                        @csrf

                        <!-- Información General -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Consecutivo No.</label>
                                    <input type="text" name="consecutivo_no" class="form-control" value="{{ 'COND-' . date('YmdHis') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Método</label>
                                    <input type="text" class="form-control" value="Determinación de Conductividad Eléctrica en Suelos" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre del Analista</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha del Análisis</label>
                                    <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proceso</label>
                                    <select name="process_id" class="form-control" required>
                                        <option value="">Seleccionar Proceso</option>
                                        @foreach ($processes as $process)
                                            <option value="{{ $process->process_id }}" {{ $selectedProcessId == $process->process_id ? 'selected' : '' }}>
                                                {{ $process->item_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Servicio</label>
                                    <select name="service_id" class="form-control" required>
                                        <option value="">Seleccionar Servicio</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->services_id }}">{{ $service->description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Equipo Utilizado</label>
                                    <input type="text" name="equipo_utilizado" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Resolución Instrumental</label>
                                    <input type="text" name="resolucion_instrumental" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unidades de Reporte Equipo</label>
                                    <input type="text" class="form-control" value="µS/cm y dS/m" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Intervalo del Método</label>
                                    <input type="text" class="form-control" value="0 a 2000 µS/cm" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Controles de Calidad -->
                        <h5 class="mt-4">Controles de Calidad</h5>
                        <h6>Blanco del Proceso</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Identificación</label>
                                    <input type="text" class="form-control" value="Blanco del proceso" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Valor Leído (µS/cm)</label>
                                    <input type="number" step="0.01" name="blanco_valor_leido" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="blanco_observaciones" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <h6>Precisión (Duplicados)</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Identificación de Muestra</label>
                                    <input type="text" name="duplicado_identificacion" class="form-control" value="Muestra Duplicada">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Duplicado A (µS/cm)</label>
                                    <input type="number" step="0.01" name="duplicado_a_valor_leido" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Duplicado B (µS/cm)</label>
                                    <input type="number" step="0.01" name="duplicado_b_valor_leido" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea name="duplicado_observaciones" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                        <h6>Veracidad</h6>
                        <div id="veracidad">
                            @foreach (['Material de Referencia Certificado', 'Estándar de Control', 'Muestra de Referencia'] as $index => $tipo)
                                <div class="row veracidad-item">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Identificación</label>
                                            <input type="text" name="veracidad[{{ $index }}][identificacion]" class="form-control" value="{{ $tipo }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Valor Esperado (dS/m)</label>
                                            <input type="number" step="0.01" name="veracidad[{{ $index }}][valor_esperado]" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Valor Leído (dS/m)</label>
                                            <input type="number" step="0.01" name="veracidad[{{ $index }}][valor_leido]" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Observaciones</label>
                                            <textarea name="veracidad[{{ $index }}][observaciones]" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <h6>Criterios de Aceptación</h6>
                        <ul>
                            <li>Blanco del proceso: ≤ 0,1 dS/m</li>
                            <li>Precisión: Conductividad eléctrica a 25 ºC de 0 mS/m a 50 mS/m Variación Aceptada 5 mS/m; >50 mS/m hasta 200 mS/m Variación aceptada 20 mS/m, >200 mS/m Variación aceptada 10%</li>
                            <li>Calidad del agua: Conductividad < 2 µS/cm a 25 °C</li>
                            <li>Veracidad: Recuperación (70 - 130)%</li>
                        </ul>

                        <!-- Información de la Muestra -->
                        <h5>Información de la Muestra</h5>
                        <div id="items-ensayo">
                            <div class="row item-ensayo">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Código Interno</label>
                                        <input type="text" name="items_ensayo[0][codigo_interno]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Peso (g)</label>
                                        <input type="number" step="0.0001" name="items_ensayo[0][peso_muestra]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Volumen H₂O (mL)</label>
                                        <input type="number" step="0.1" name="items_ensayo[0][volumen_agua]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Temperatura (°C)</label>
                                        <input type="number" step="0.1" name="items_ensayo[0][temperatura]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Lectura (µS/cm)</label>
                                        <input type="number" step="0.01" name="items_ensayo[0][lectura_uscm]" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <textarea name="items_ensayo[0][observaciones]" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addItemEnsayo()">Agregar Muestra</button>

                        <!-- Observaciones del Analista -->
                        <div class="form-group">
                            <label>Observaciones del Analista</label>
                            <textarea name="observaciones_analista" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Análisis</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        let itemEnsayoCount = 1;

        function addItemEnsayo() {
            const container = document.getElementById('items-ensayo');
            const newItem = document.createElement('div');
            newItem.classList.add('row', 'item-ensayo');
            newItem.innerHTML = `
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="items_ensayo[${itemEnsayoCount}][codigo_interno]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Peso (g)</label>
                        <input type="number" step="0.0001" name="items_ensayo[${itemEnsayoCount}][peso_muestra]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Volumen H₂O (mL)</label>
                        <input type="number" step="0.1" name="items_ensayo[${itemEnsayoCount}][volumen_agua]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Temperatura (°C)</label>
                        <input type="number" step="0.1" name="items_ensayo[${itemEnsayoCount}][temperatura]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Lectura (µS/cm)</label>
                        <input type="number" step="0.01" name="items_ensayo[${itemEnsayoCount}][lectura_uscm]" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="items_ensayo[${itemEnsayoCount}][observaciones]" class="form-control"></textarea>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            itemEnsayoCount++;
        }
    </script>
@endsection