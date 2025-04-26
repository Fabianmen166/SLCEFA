@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Revisión de Análisis: {{ $analysis->details['internal_code'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Análisis</h3>
                </div>
                <div class="card-body">
                    <p><strong>Proceso:</strong> {{ $analysis->process->item_code }}</p>
                    <p><strong>Servicio:</strong> {{ $analysis->service->description }}</p>
                    <p><strong>Fecha del Análisis:</strong> {{ $analysis->analysis_date }}</p>
                    <p><strong>Analista:</strong> {{ $analysis->details['analyst_name'] }}</p>
                    <h5>Detalles:</h5>
                    <ul>
                        @foreach ($analysis->details as $key => $value)
                            @if ($key !== 'analyst_name' && $key !== 'internal_code')
                                <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                            @endif
                        @endforeach
                    </ul>

                    <h5>Revisión Anterior:</h5>
                    @if ($analysis->review_date)
                        <p><strong>Revisado por:</strong> {{ $analysis->reviewed_by }} ({{ $analysis->reviewer_role }})</p>
                        <p><strong>Fecha de Revisión:</strong> {{ $analysis->review_date }}</p>
                        <p><strong>Estado:</strong> {{ ucfirst($analysis->review_status) }}</p>
                        <p><strong>Observaciones:</strong> {{ $analysis->review_observations ?? 'Ninguna' }}</p>
                    @else
                        <p>No revisado aún.</p>
                    @endif

                    <form action="{{ route('process.store_review', $analysis->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="review_status">Estado de Revisión:</label>
                            <select name="review_status" id="review_status" class="form-control @error('review_status') is-invalid @enderror" required>
                                <option value="approved">Aprobar</option>
                                <option value="rejected">Rechazar</option>
                            </select>
                            @error('review_status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="review_observations">Observaciones:</label>
                            <textarea name="review_observations" id="review_observations" class="form-control @error('review_observations') is-invalid @enderror">{{ old('review_observations') }}</textarea>
                            @error('review_observations')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Revisión</button>
                        <a href="{{ route('process.review_index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection