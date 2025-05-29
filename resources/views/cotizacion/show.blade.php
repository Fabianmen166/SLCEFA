@extends('layouts.master')

@section('contenido')
    <center>
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%" alt="Logo Agrosoft">
    </center>

    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <br><br>

        <center>
            <div class="container">
                <div class="card">
                    <h5 class="card-header">Detalles de la Cotización #{{ $quote->quote_id ?? 'N/A' }}</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <p><strong>NIT:</strong> {{ $quote->customer->nit ?? 'N/A' }}</p>
                                <p><strong>Solicitante:</strong> {{ $quote->customer->solicitante ?? 'N/A' }}</p>
                                <p><strong>Tipo de Cliente:</strong> {{ ucfirst($quote->customer->tipo_cliente) ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Información de la Cotización</h6>
                                <p><strong>ID de Cotización:</strong> {{ $quote->quote_id }}</p>
                                <p><strong>Creado por:</strong> {{ $quote->user->name ?? 'N/A' }}</p>
                                <p><strong>Total:</strong> {{ number_format($quote->total, 2) }}</p>
                                @if ($quote->archivo)
                                    <p><strong>Comprobante:</strong> <a href="{{ Storage::url('comprobantes/' . $quote->archivo) }}" target="_blank">Ver Comprobante</a></p>
                                @else
                                    <p><strong>Comprobante:</strong> No subido</p>
                                @endif
                            </div>
                        </div>

                        <h6 class="mt-4">Servicios y Paquetes por Terreno</h6>
                        @php
                            $servicesPerUnit = [];
                            foreach ($quote->quoteServices as $qs) {
                                $unitIdx = $qs->unit_index ?? 0;
                                $servicesPerUnit[$unitIdx][] = $qs;
                            }
                        @endphp
                        @if (!empty($servicesPerUnit))
                            @foreach ($servicesPerUnit as $unitIndex => $unitServices)
                                <h6 class="mt-3">Terreno {{ $unitIndex + 1 }}</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        @foreach ($unitServices as $quoteService)
                                        @if ($quoteService->services_id && $quoteService->service)
                                            <tr>
                                                <td>Servicio</td>
                                                <td>{{ $quoteService->service->descripcion ?? 'Servicio no encontrado' }}</td>
                                                <td>{{ $quoteService->cantidad }}</td>
                                                <td>{{ number_format($quoteService->subtotal, 2) }}</td>
                                            </tr>
                                        @elseif ($quoteService->service_packages_id && $quoteService->servicePackage)
                                            <tr>
                                                <td>Paquete</td>
                                                <td>{{ $quoteService->servicePackage->nombre ?? 'Paquete no encontrado' }}</td>
                                                <td>{{ $quoteService->cantidad }}</td>
                                                <td>{{ number_format($quoteService->subtotal, 2) }}</td>
                                            </tr>
                                            @if ($quoteService->servicePackage && $quoteService->servicePackage->included_services)
                                                @foreach ($quoteService->servicePackage->included_services as $includedService)
                                                    @if (is_array($includedService) || is_object($includedService))
                                                        <?php
                                                            $description = is_object($includedService) ? $includedService->description ?? $includedService->descripcion ?? 'Descripción no disponible' : $includedService;
                                                        ?>
                                                        <tr class="table-light">
                                                            <td>Incluido</td>
                                                            <td>↳ {{ $description }}</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                        </tr>
                                                    @else
                                                        <tr class="table-light">
                                                            <td>Incluido</td>
                                                            <td>↳ {{ $includedService }}</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            @endforeach
                        @else
                            <p>Sin servicios ni paquetes.</p>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('cotizacion.comprobante', $quote->quote_id) }}" class="btn btn-primary">Descargar PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        </center>
    </div>
@endsection