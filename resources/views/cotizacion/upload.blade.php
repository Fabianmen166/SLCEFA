@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Subir Comprobante para {{ $quote->quote_id }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('gestion_calidad.dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cotizacion.index') }}">Cotizaciones</a></li>
                        <li class="breadcrumb-item active">Subir Comprobante</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Subir Comprobante</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('cotizacion.upload.store', $quote->quote_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="archivo">Archivo (PDF, JPG, PNG):</label>
                            <input type="file" name="archivo" id="archivo" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Subir</button>
                        <a href="{{ route('cotizacion.index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection