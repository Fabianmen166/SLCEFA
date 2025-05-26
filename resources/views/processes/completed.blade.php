<!-- resources/views/processes/completed.blade.php -->
@extends('layouts.master1')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Procesos Completados (Admin)</h1>
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
                    <h3 class="card-title">Procesos Listos para Informe</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Cotización</th>
                                <th>Cliente</th>
                                <th>Análisis Completados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($processes as $process)
                                <tr>
                                    <td>{{ $process->process_id }}</td>
                                    <td>{{ $process->quote->quote_id ?? 'N/A' }}</td>
                                    <td>{{ $process->quote->customer->contacto ?? 'Sin cliente' }}</td>
                                    <td>{{ $process->analyses->count() }}</td>
                                    <td>
                                        <a href="{{ route('process.preview_report', $process->process_id) }}" class="btn btn-info btn-sm" target="_blank">Previsualizar Informe</a>
                                        <a href="{{ route('process.generate_report', $process->process_id) }}" class="btn btn-success btn-sm">Descargar Informe</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No hay procesos completados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $processes->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection