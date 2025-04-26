@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Revisión de Análisis (Admin)</h1>
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
                    <h3 class="card-title">Lista de Análisis</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Servicio</th>
                                <th>Fecha del Análisis</th>
                                <th>Código Interno</th>
                                <th>Estado de Revisión</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($analyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->process->item_code }}</td>
                                    <td>{{ $analysis->service->description }}</td>
                                    <td>{{ $analysis->analysis_date }}</td>
                                    <td>{{ $analysis->details['internal_code'] }}</td>
                                    <td>
                                        @if ($analysis->review_status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif ($analysis->review_status === 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @else
                                            <span class="badge badge-danger">Rechazado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('process.review_analysis', $analysis->id) }}" class="btn btn-primary btn-sm">Revisar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $analyses->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection