@extends('layouts.app')

@section('title', 'Gestión de Análisis de Intercambio Catiónico')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Intercambio Catiónico</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Análisis de Intercambio Catiónico</li>
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

            <div class="row mb-3">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" id="processSelectedBtn" style="display: none;">Procesar Seleccionados</button>
                    <button type="button" class="btn btn-secondary" id="clearSelectionBtn" style="display: none;">Limpiar Selección</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID Proceso</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processes as $process)
                                    @foreach($process->analyses as $analysis)
                                        <tr data-analysis-id="{{ $analysis->id }}" data-service-type="cation_exchange">
                                            <td><input type="checkbox" class="analysis-checkbox" value="{{ $analysis->id }}"></td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $analysis->service->descripcion ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('cation_exchange_analysis.process', ['processId' => $process->process_id, 'serviceId' => $analysis->service_id]) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    Procesar Análisis
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay análisis pendientes</td>
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

            $('.analysis-checkbox:checked').each(function() {
                selectedAnalyses.push($(this).val());
            });

            if (selectedAnalyses.length === 0) {
                alert('Por favor, selecciona al menos un análisis para procesar.');
                return;
            }

            var baseUrl = '{{ route('cation_exchange_analysis.batch_process') }}';

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