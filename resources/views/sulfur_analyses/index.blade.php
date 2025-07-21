@extends('layouts.app')

@section('title', 'Gestión de Análisis de Azufre')

@section('contenido')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Análisis de Azufre</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('personal_tecnico.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Gestión de Azufre</li>
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
                    <h3 class="card-title">Análisis de Azufre Pendientes</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
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
                                            <td><input type="checkbox" class="select_analysis" value="{{ $analysis->id }}"></td>
                                            <td>{{ $process->process_id }}</td>
                                            <td>{{ $process->quote->customer->nombre }}</td>
                                            <td>{{ $process->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('sulfur_analysis.sulfur_analysis', [$process->process_id, $analysis->service_id]) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-flask"></i> Realizar Análisis
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay análisis de azufre pendientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button id="batch_process_btn" class="btn btn-success mt-2" disabled>Procesar por Lotes</button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 

@push('scripts')
<script>
    $(document).ready(function() {
        $('#select_all').on('change', function() {
            $('.select_analysis').prop('checked', $(this).prop('checked'));
            $('#batch_process_btn').prop('disabled', $('.select_analysis:checked').length === 0);
        });
        $(document).on('change', '.select_analysis', function() {
            $('#batch_process_btn').prop('disabled', $('.select_analysis:checked').length === 0);
        });
        $('#batch_process_btn').click(function() {
            let selected = $('.select_analysis:checked').map(function() { return $(this).val(); }).get();
            if(selected.length > 0) {
                let url = "{{ route('sulfur_analysis.batch_process') }}" + '?analysis_ids=' + selected.join(',');
                window.location.href = url;
            }
        });
    });
</script>
@endpush 