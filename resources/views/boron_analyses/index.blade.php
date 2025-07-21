@extends('layouts.app')

@section('title', 'Gestión de Análisis de Boro')

@section('contenido')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Boro</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Gestión de Boro</li>
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

            <!-- Formulario oculto para batch -->
            <form id="batchProcessForm" method="POST" action="{{ route('boron_analysis.boron_analysis', ['multi', 'multi']) }}" style="display:none;">
                @csrf
                <input type="hidden" name="analysis_ids" id="analysis_ids_input">
            </form>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis de Boro Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>ID Proceso</th>
                                    <th>Cliente</th>
                                    <th>Fecha de Solicitud</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processes as $process)
                                    @foreach($process->analyses as $analysis)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="process-checkbox" value="{{ $analysis->id }}">
                                            </td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $process->quote->customer->nombre }}</td>
                                            <td>{{ $process->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('boron_analysis.boron_analysis', [$process->process_id, $analysis->service_id]) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-flask"></i> Realizar Análisis
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay análisis de boro pendientes</td>
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Select All functionality
        $('#selectAll').change(function() {
            $('.process-checkbox').prop('checked', $(this).prop('checked'));
            updateProcessButtons();
        });

        $('.process-checkbox').change(function() {
            updateProcessButtons();
        });

        function updateProcessButtons() {
            var checkedCount = $('.process-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#processSelectedBtn, #clearSelectionBtn').show();
            } else {
                $('#processSelectedBtn, #clearSelectionBtn').hide();
            }
        }

        $('#clearSelectionBtn').click(function() {
            $('.process-checkbox, #selectAll').prop('checked', false);
            updateProcessButtons();
        });

        // Procesar seleccionados (batch)
        $('#processSelectedBtn').click(function() {
            var selected = $('.process-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (selected.length === 0) {
                alert('Debes seleccionar al menos un análisis.');
                return;
            }
            if (selected.length > 10) {
                alert('Solo puedes procesar un máximo de 10 análisis a la vez.');
                return;
            }
            // Redirigir por GET con los IDs a la ruta de proceso principal
            var url = "{{ route('boron_analysis.batch_process') }}" + "?" + selected.map(function(id) { return "analysis_ids[]=" + encodeURIComponent(id); }).join("&");
            window.location.href = url;
        });
    });
</script>
@endpush
@endsection 