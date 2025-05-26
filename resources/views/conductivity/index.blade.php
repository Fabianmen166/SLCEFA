@extends('layouts.app')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Análisis de Conductividad Pendientes (Personal Técnico)</h1>
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
                    <h3 class="card-title">Lista de Análisis de Conductividad Pendientes</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Servicio</th>
                                <th>Fecha del Análisis</th>
                                <th>Código Interno</th>
                                <th>Resultado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($analyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->process_item_code }}</td>
                                    <td>{{ $analysis->description }}</td>
                                    <td>{{ $analysis->analysis_date ?? 'N/A' }}</td>
                                    <td>{{ $analysis->details['internal_code'] }}</td>
                                    <td>{{ $analysis->result }}</td>
                                    <td>
                                        <span class="badge badge-warning">Pendiente</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('conductivity.create') }}?process_id={{ $analysis->process_id }}" class="btn btn-primary btn-sm">Procesar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No tienes análisis de conductividad pendientes por procesar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $analyses->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection