@extends('layouts.app')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Procesos Pendientes</h1>
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Procesos Pendientes</h3>
                </div>
                <div class="card-body">
                    @if($processes->isEmpty())
                        <p>No hay procesos pendientes.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Código del Ítem</th>
                                    <th>Servicios Pendientes</th>
                                    <th>Servicios Realizados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($processes as $process)
                                    <tr>
                                        <td>{{ $process->item_code }}</td>
                                        <td>
                                            @if($process->services->isEmpty())
                                                <p>No hay servicios pendientes.</p>
                                            @else
                                                <ul>
                                                    @foreach ($process->services as $service)
                                                        <li>
                                                            {{ $service->descripcion }} (Cantidad: {{ $service->pivot->cantidad ?? 'No especificada' }})
                                                            @php
                                                                $analyses = $process->serviceProcessDetails->where('services_id', $service->id);
                                                            @endphp
                                                            @if ($analyses->where('review_status', 'rejected')->count() > 0)
                                                                <span class="badge badge-danger">Análisis Rechazado</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            @if($process->completedServices->isEmpty())
                                                <p>No hay servicios realizados.</p>
                                            @else
                                                <ul>
                                                    @foreach ($process->completedServices as $service)
                                                        <li>
                                                            {{ $service->descripcion }} (Cantidad: {{ $service->pivot->cantidad ?? 'No especificada' }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('process.technical_analysis', $process->process_id) }}" class="btn btn-primary btn-sm">Realizar Análisis</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection