@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <h1>Subir Comprobante y Gestionar Proceso para Cotización {{ $quote->quote_id }}</h1>
    </div>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <!-- Formulario para Subir Comprobante -->
                <h3>Subir Comprobante de Pago</h3>
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('cotizacion.upload', $quote->quote_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="archivo">Seleccionar Comprobante:</label>
                        <input type="file" name="archivo" id="archivo" class="form-control" required>
                        @error('archivo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Subir Comprobante</button>
                    <a href="{{ route('cotizacion.index') }}" class="btn btn-secondary">Volver</a>
                </form>

                <!-- Formulario para Iniciar Proceso -->
                <hr>
                <h3>Iniciar Proceso</h3>
                @if ($quote->processes()->exists())
                    <div class="alert alert-info">
                        Ya se ha iniciado un proceso para esta cotización.
                        <a href="{{ route('processes.show', $quote->processes()->first()->process_id) }}" class="btn btn-info btn-sm">Ver Proceso</a>
                    </div>
                @else
                    <form action="{{ route('cotizacion.process.start', $quote->quote_id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="item_code">Código del Ítem:</label>
                            <input type="text" name="item_code" id="item_code" class="form-control" value="{{ old('item_code') }}" required>
                            @error('item_code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comunicacion_cliente">Comunicación con el Cliente (Opcional):</label>
                            <textarea name="comunicacion_cliente" id="comunicacion_cliente" class="form-control" rows="3">{{ old('comunicacion_cliente') }}</textarea>
                            @error('comunicacion_cliente')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dias_procesar">Días para Procesar:</label>
                            <input type="number" name="dias_procesar" id="dias_procesar" class="form-control" value="{{ old('dias_procesar') }}" min="1" required>
                            @error('dias_procesar')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción (Opcional):</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="lugar_muestreo">Lugar de Muestreo (Opcional):</label>
                            <input type="text" name="lugar_muestreo" id="lugar_muestreo" class="form-control" value="{{ old('lugar_muestreo') }}">
                            @error('lugar_muestreo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="fecha_muestreo">Fecha de Muestreo (Opcional):</label>
                            <input type="date" name="fecha_muestreo" id="fecha_muestreo" class="form-control" value="{{ old('fecha_muestreo') }}">
                            @error('fecha_muestreo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">Iniciar Proceso</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection