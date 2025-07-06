@extends('layouts.app')

@section('contenido')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Bases Cambiables - Análisis Pendientes</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Análisis pendientes</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Proceso</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analyses as $a)
                                    <tr>
                                        <td>{{ $a->id }}</td>
                                        <td>{{ $a->process->process_id ?? '' }}</td>
                                        <td>{{ $a->service->descripcion ?? '' }}</td>
                                        <td>
                                            @if($a->status === 'pending')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @elseif($a->status === 'completed')
                                                <span class="badge badge-success">Completado</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($a->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('bases_cambiables_analysis.process', ['processId' => $a->process_id, 'serviceId' => $a->service_id]) }}" class="btn btn-warning btn-sm">Procesar análisis</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">No hay análisis pendientes de bases cambiables.</td></tr>
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