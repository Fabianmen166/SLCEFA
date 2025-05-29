@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <h1>Subir Comprobante y Gestionar Proceso para Cotización {{ $quote->quote_id }}</h1>
    </div>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <!-- Formulario para Subir Comprobante -->
                <h3>Subir Comprobante de Pago</h3>
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('cotizacion.upload', $quote->quote_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="archivo">Seleccionar Comprobante:</label>
                        <input type="file" name="archivo" id="archivo" class="form-control" required>
                        @error('archivo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Subir Comprobante</button>
                    <a href="{{ route('cotizacion.index') }}" class="btn btn-secondary">Volver</a>
                </form>

                <!-- Sección para Gestionar Procesos por Terreno -->
                <hr>
                <h3>Gestionar Procesos por Terreno</h3>

                @php
                    // Usar el unitCount pasado desde el controlador
                    $unitCount = $unitCount ?? 1;
                    if ($unitCount < 1) $unitCount = 1;

                    // Obtener procesos existentes
                    $existingProcesses = $quote->processes->keyBy('descripcion')->all();

                    // Agrupar los servicios por unit_index
                    $quoteServices = $quote->quoteServices ?? collect();
                    $servicesPerUnit = [];
                    foreach ($quoteServices as $quoteService) {
                        $unitIdx = $quoteService->unit_index ?? 0;
                        $servicesPerUnit[$unitIdx][] = $quoteService;
                    }
                @endphp

                @if ($unitCount == 0)
                    <div class="alert alert-warning">
                        No se han definido terrenos para esta cotización.
                    </div>
                @else
                    <!-- Formulario único para todos los terrenos -->
                    <form action="{{ route('cotizacion.process.start', $quote->quote_id) }}" method="POST" id="process-form">
                        @csrf
                        <input type="hidden" name="unit_count" value="{{ $unitCount }}">

                        <!-- Campos compartidos para todos los terrenos -->
                        <div class="form-group">
                            <label for="comunicacion_cliente">Comunicación con el Cliente (Opcional):</label>
                            <textarea name="comunicacion_cliente" id="comunicacion_cliente" class="form-control" rows="3">{{ old('comunicacion_cliente') }}</textarea>
                            @error('comunicacion_cliente')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dias_procesar">Días para Procesar:</label>
                            <input type="number" name="dias_procesar" id="dias_procesar" class="form-control" value="{{ old('dias_procesar', 5) }}" min="1" required>
                            @error('dias_procesar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="lugar_muestreo">Lugar de Muestreo (Opcional):</label>
                            <input type="text" name="lugar_muestreo" id="lugar_muestreo" class="form-control" value="{{ old('lugar_muestreo') }}">
                            @error('lugar_muestreo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="fecha_muestreo">Fecha de Muestreo (Opcional):</label>
                            <input type="date" name="fecha_muestreo" id="fecha_muestreo" class="form-control" value="{{ old('fecha_muestreo') }}">
                            @error('fecha_muestreo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Terrenos con descripción, item_code y servicios específicos -->
                        @foreach (range(0, $unitCount - 1) as $unitIndex)
                            @php
                                $suggestedDescription = 'TERRENO-' . ($unitIndex + 1);
                                $suggestedItemCode = 'TERRENO-' . ($unitIndex + 1);
                                $process = $existingProcesses[$suggestedDescription] ?? null;
                                $unitServices = $servicesPerUnit[$unitIndex] ?? [];
                                // Guardar los IDs de los servicios asignados a este terreno
                                $unitServiceIds = array_map(fn($service) => $service->id, $unitServices);
                            @endphp

                            <div class="mt-4" data-unit-index="{{ $unitIndex }}">
                                <h4>Terreno {{ $unitIndex + 1 }} ({{ $quote->quote_id }})</h4>

                                @if ($process)
                                    <div class="alert alert-info">
                                        Proceso iniciado para este terreno. Estado: {{ ucfirst($process['status']) }}.
                                        <br><strong>Identificado por:</strong> {{ $process['descripcion'] }} ({{ $process['item_code'] }})
                                        <br><a href="{{ route('processes.show', $process['process_id']) }}" class="btn btn-info btn-sm mt-2">Ver Proceso</a>
                                    </div>
                                @endif

                                <!-- Servicios asignados al terreno -->
                                <div class="form-group">
                                    <label>Servicios Asociados al Terreno {{ $unitIndex + 1 }}:</label>
                                    @if (empty($unitServices))
                                        <p class="text-muted">No hay servicios asignados a este terreno.</p>
                                    @else
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Descripción</th>
                                                    <th>Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unitServices as $quoteService)
                                                    <tr>
                                                        <td>
                                                            @if ($quoteService->services_id)
                                                                @php
                                                                    $service = $quoteService->service;
                                                                    if ($service instanceof \Illuminate\Database\Eloquent\Collection) {
                                                                        dd([
                                                                            'error' => 'Service es una colección en lugar de un objeto',
                                                                            'quote_service_id' => $quoteService->id,
                                                                            'services_id' => $quoteService->services_id,
                                                                            'service_data' => $service->toArray()
                                                                        ]);
                                                                    } elseif ($service) {
                                                                        echo $service->descripcion;
                                                                    } else {
                                                                        echo 'Servicio no encontrado';
                                                                    }
                                                                @endphp
                                                            @elseif ($quoteService->service_packages_id)
                                                                @php
                                                                    $package = $quoteService->servicePackage;
                                                                    if ($package) {
                                                                        echo $package->nombre;
                                                                    } else {
                                                                        echo 'Paquete no encontrado';
                                                                    }
                                                                @endphp
                                                            @else
                                                                Detalles no disponibles
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $quoteService->cantidad ?? 'N/A' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>

                                @if (!$process)
                                    <div class="form-group">
                                        <label for="item_code_{{ $unitIndex }}">Código del Ítem para Terreno {{ $unitIndex + 1 }}:</label>
                                        <input type="text" name="item_codes[{{ $unitIndex }}]" id="item_code_{{ $unitIndex }}" class="form-control" value="{{ old('item_codes.' . $unitIndex, $suggestedItemCode) }}" required>
                                        @error('item_codes.' . $unitIndex)
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="descripcion_{{ $unitIndex }}">Descripción para Terreno {{ $unitIndex + 1 }}:</label>
                                        <textarea name="descriptions[{{ $unitIndex }}]" id="descripcion_{{ $unitIndex }}" class="form-control" rows="3">{{ old('descriptions.' . $unitIndex, $suggestedDescription) }}</textarea>
                                        @error('descriptions.' . $unitIndex)
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Campo oculto para enviar los IDs de los servicios asignados -->
                                    <input type="hidden" name="services[{{ $unitIndex }}]" value="{{ json_encode($unitServiceIds) }}" />
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success mt-4">Iniciar Procesos para Todos los Terrenos</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection