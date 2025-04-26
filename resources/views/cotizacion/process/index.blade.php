@extends('layouts.master')

@section('title', 'Procesos Abiertos')

@section('contenido')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Procesos Abiertos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    
                    <li class="breadcrumb-item active">Procesos Abiertos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Procesos</h3>
            </div>
            <div class="card-body">
                @if($processes->isEmpty())
                    <p>No hay procesos pendientes.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Proceso</th>
                                <th>Cotización</th>
                                <th>Servicios/Paquetes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processes as $process)
                                <tr>
                                    <td>{{ $process->process_id }}</td>
                                    <td>{{ $process->quote->quote_id }}</td>
                                    <td>
                                        <ul>
                                            @foreach($process->quote->quoteServices as $quoteService)
                                                <li>
                                                    @if($quoteService->service)
                                                        {{ $quoteService->service->descripcion }} (Cantidad: {{ $quoteService->cantidad }})
                                                    @elseif($quoteService->servicePackage)
                                                        {{ $quoteService->servicePackage->nombre }} (Cantidad: {{ $quoteService->cantidad }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <a href="{{ route('cotizacion.process.show', $process->process_id) }}" class="btn btn-sm btn-primary">Ver Detalles</a>
                                        <form action="{{ route('cotizacion.process.destroy', $process->process_id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este proceso?')">Eliminar</button>
                                        </form>
                                        @if($process->status == 'completed')
                                            <form action="{{ route('cotizacion.process.archive', $process->process_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Archivar</button>
                                            </form>
                                            @if(Auth::user()->hasRole('admin'))
                                                <a href="{{ route('cotizacion.process.results_pdf', $process->process_id) }}" class="btn btn-sm btn-success">Generar PDF de Resultados</a>
                                            @endif
                                        @endif
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