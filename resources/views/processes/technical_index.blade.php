@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Procesos Pendientes (Personal Técnico)</h1>
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
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Cotización</th>
                                <th>Código del Ítem</th>
                                <th>Servicios Pendientes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($processes as $process)
                                <tr>
                                    <td>{{ $process->quote->id }}</td>
                                    <td>{{ $process->item_code }}</td>
                                    <td>
                                        <ul>
                                            @foreach ($process->services as $service)
                                                @if ($service->pivot->status === 'pending')
                                                    <li>
                                                        {{ $service->description }} (Cantidad: {{ $service->pivot->cantidad }})
                                                        @php
                                                            $analyses = $process->serviceDetails->where('services_id', $service->services_id);
                                                        @endphp
                                                        @if ($analyses->where('review_status', 'rejected')->count() > 0)
                                                            <span class="badge badge-danger">Análisis Rechazado</span>
                                                        @endif
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <a href="{{ route('process.technical_analysis', $process->process_id) }}" class="btn btn-primary btn-sm">Realizar Análisis</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection