@extends('layouts.app')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Procesos Técnicos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Procesos Técnicos</li>
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Procesos Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="processSelectedBtn">Procesar Seleccionados</button>
                            <button type="button" class="btn btn-secondary" id="clearSelectionBtn" style="display: none;">Limpiar Selección</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID Proceso</th>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processes as $process)
                                    @foreach($process->analyses as $analysis)
                                        <tr data-analysis-id="{{ $analysis->id }}" data-service-type="{{ str_contains(strtolower($analysis->service->descripcion ?? ''), 'ph') ? 'ph' : (str_contains(strtolower($analysis->service->descripcion ?? ''), 'intercambio catiónico') ? 'cation_exchange' : 'other') }}">
                                            <td><input type="checkbox" class="analysis-checkbox" value="{{ $analysis->id }}"></td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $process->quote->customer->nombre ?? 'N/A' }}</td>
                                            <td>{{ $analysis->service->descripcion ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                @if(str_contains(strtolower($analysis->service->descripcion ?? ''), 'ph'))
                                                    <a href="{{ route('ph_analysis.ph_analysis', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        Procesar pH
                                                    </a>
                                                @elseif(str_contains(strtolower($analysis->service->descripcion ?? ''), 'conductividad'))
                                                    <a href="{{ route('conductivity.create', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                       class="btn btn-info btn-sm">
                                                        Procesar Conductividad
                                                    </a>
                                                @elseif(str_contains(strtolower($analysis->service->descripcion ?? ''), 'intercambio catiónico'))
                                                    <a href="{{ route('cation_exchange_analysis.process', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                       class="btn btn-success btn-sm">
                                                        Procesar Intercambio Catiónico
                                                    </a>
                                                @elseif(str_contains(strtolower($analysis->service->descripcion ?? ''), 'base cambiable') || str_contains(strtolower($analysis->service->descripcion ?? ''), 'bases cambiables'))
                                                    <a href="{{ route('bases_cambiables_analysis.process', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                       class="btn btn-warning btn-sm">
                                                        Procesar Bases Cambiables
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay procesos pendientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function updateProcessButtonState() {
            var checkedCount = $('.analysis-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#processSelectedBtn').show();
                $('#clearSelectionBtn').show();
            } else {
                $('#processSelectedBtn').hide();
                $('#clearSelectionBtn').hide();
            }
        }

        $('#selectAll').on('change', function() {
            $('.analysis-checkbox').prop('checked', $(this).prop('checked'));
            updateProcessButtonState();
        });

        $('.analysis-checkbox').on('change', function() {
            var allChecked = $('.analysis-checkbox:checked').length === $('.analysis-checkbox').length;
            $('#selectAll').prop('checked', allChecked);
            updateProcessButtonState();
        });

        $('#clearSelectionBtn').on('click', function() {
            $('.analysis-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateProcessButtonState();
        });

        $('#processSelectedBtn').on('click', function() {
            var selectedAnalyses = [];
            var serviceTypes = new Set();

            $('.analysis-checkbox:checked').each(function() {
                var row = $(this).closest('tr');
                var analysisId = row.data('analysis-id');
                var serviceType = row.data('service-type');

                selectedAnalyses.push(analysisId);
                serviceTypes.add(serviceType);
            });

            if (selectedAnalyses.length === 0) {
                alert('Por favor, selecciona al menos un análisis para procesar.');
                return;
            }

            if (serviceTypes.size > 1) {
                alert('Solo puedes procesar análisis del mismo tipo (pH o Intercambio Catiónico) en un lote.');
                return;
            }

            var type = serviceTypes.values().next().value;
            var baseUrl = '';
            if (type === 'ph') {
                baseUrl = '{{ route('ph_analysis.batch_process') }}';
            } else if (type === 'cation_exchange') {
                baseUrl = '{{ route('cation_exchange_analysis.batch_process') }}';
            } else {
                alert('Tipo de servicio no soportado para procesamiento por lotes.');
                return;
            }

            var form = $('<form>').attr({
                method: 'GET',
                action: baseUrl
            });
            
            $.each(selectedAnalyses, function(index, id) {
                form.append($('<input>').attr({
                    type: 'hidden',
                    name: 'analysis_ids[]',
                    value: id
                }));
            });

            $('body').append(form);
            form.submit();
        });

        updateProcessButtonState(); // Initial state on page load
    });
</script>
@endpush