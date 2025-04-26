@extends('layouts.master')

@section('contenido')
<center>
    <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%">
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
                <h5 class="card-header">Editar Cotización #{{ $quote->quote_id ?? 'N/A' }}</h5>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('cotizacion.update', $quote->quote_id) }}" id="edit-quote-form">                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="quote_id" class="form-label">ID de la Cotización</label>
                            <input type="text" class="form-control" id="quote_id" name="quote_id" value="{{ old('quote_id', $quote->quote_id) }}">
                            @error('quote_id')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customers_id" class="form-label">Cliente (NIT)</label>
                            <select class="form-control" id="customers_id" name="customers_id" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach ($customers as $customer)
                                <option value="{{ $customer->customers_id }}" {{ old('customers_id', $quote->customers_id) == $customer->customers_id ? 'selected' : '' }}>
                                    {{ $customer->nit }} - {{ $customer->solicitante }} ({{ ucfirst($customer->tipo_cliente) }})
                                </option>
                                @endforeach
                            </select>
                            @error('customers_id')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <h5>Servicios y Paquetes</h5>
                        <div id="services-container">
                            @foreach ($quote->quoteServices as $index => $quoteService)
                            <div class="service-row mb-3" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="services_{{ $index }}" class="form-label">Servicio o Paquete</label>
                                        @if ($quoteService->services_id)
                                        @if ($quoteService->service)
                                        <select class="form-control service-select" name="services[{{ $index }}]" id="services_{{ $index }}">
                                            <option value="">Seleccione un servicio</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}"
                                                {{ $service->services_id == $quoteService->services_id ? 'selected' : '' }}>
                                                {{ $service->descripcion }} ({{ $service->precio }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <select class="form-control package-select" name="service_packages[{{ $index }}]" id="service_packages_{{ $index }}" style="display: none;">
                                            <option value="">Seleccione un paquete</option>
                                        </select>
                                        @else
                                        <div class="alert alert-warning">
                                            Servicio no encontrado (ID: {{ $quoteService->services_id }}). Seleccione otro.
                                        </div>
                                        <select class="form-control service-select" name="services[{{ $index }}]" id="services_{{ $index }}">
                                            <option value="">Seleccione un servicio</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">
                                                {{ $service->descripcion }} ({{ $service->precio }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <select class="form-control package-select" name="service_packages[{{ $index }}]" id="service_packages_{{ $index }}" style="display: none;">
                                            <option value="">Seleccione un paquete</option>
                                        </select>
                                        @endif
                                        @elseif ($quoteService->service_packages_id)
                                        @if ($quoteService->servicePackage)
                                        <select class="form-control service-select" name="services[{{ $index }}]" id="services_{{ $index }}" style="display: none;">
                                            <option value="">Seleccione un servicio</option>
                                        </select>
                                        <select class="form-control package-select" name="service_packages[{{ $index }}]" id="service_packages_{{ $index }}">
                                            <option value="">Seleccione un paquete</option>
                                            @foreach ($servicePackages as $servicePackage)
                                            <option value="{{ $servicePackage->service_packages_id }}" data-precio="{{ $servicePackage->precio }}"
                                                {{ $servicePackage->service_packages_id == $quoteService->service_packages_id ? 'selected' : '' }}>
                                                {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @else
                                        <div class="alert alert-warning">
                                            Paquete no encontrado (ID: {{ $quoteService->service_packages_id }}). Seleccione otro.
                                        </div>
                                        <select class="form-control service-select" name="services[{{ $index }}]" id="services_{{ $index }}" style="display: none;">
                                            <option value="">Seleccione un servicio</option>
                                        </select>
                                        <select class="form-control package-select" name="service_packages[{{ $index }}]" id="service_packages_{{ $index }}">
                                            <option value="">Seleccione un paquete</option>
                                            @foreach ($servicePackages as $servicePackage)
                                            <option value="{{ $servicePackage->service_packages_id }}" data-precio="{{ $servicePackage->precio }}">
                                                {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @endif
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <label for="quantities_{{ $index }}" class="form-label">Cantidad</label>
                                        <input type="number" class="form-control quantity-input" name="quantities[{{ $index }}]" id="quantities_{{ $index }}" min="1" value="{{ old('quantities.' . $index, $quoteService->cantidad) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal-input" readonly value="{{ number_format($quoteService->subtotal, 2) }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-service-btn">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-secondary mb-3" id="add-service-btn">Agregar Otro Servicio</button>
                        <button type="button" class="btn btn-secondary mb-3" id="add-package-btn">Agregar Otro Paquete</button>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5>Total: <span id="total-amount">{{ number_format($quote->total, 2) }}</span></h5>
                            </div>
                        </div>

                        <br>
                        <button type="submit" class="btn btn-primary">Actualizar Cotización</button>
                        <a href="{{ route('cotizacion.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
            <a href="{{ route('gestion_calidad.dashboard') }}" class="back-btn">Volver al Dashboard</a>
        </div>
    </center>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@php
$packagesData = [];
try {
if ($servicePackages instanceof \Illuminate\Support\Collection && $servicePackages->isNotEmpty()) {
$packagesData = $servicePackages->mapWithKeys(function ($package) {
$includedServices = $package->included_service_ids ?? [];
return [$package->service_packages_id => $includedServices];
})->toArray();
}
} catch (\Exception $e) {
Log::error('Error al calcular packagesData: ' . $e->getMessage());
$packagesData = [];
}
@endphp
<div id="services-data" style="display: none;"
    data-services="{{ json_encode($services) }}"
    data-service-packages="{{ json_encode($servicePackages) }}"
    data-packages-data="{{ json_encode($packagesData) }}"></div>

<script>
    document.getElementById('edit-quote-form').addEventListener('submit', function(event) {
        event.preventDefault();

        let form = this;
        let formData = new FormData(form);

        fetch(form.action, {
                method: 'POST', // Laravel expects POST for PUT requests with _method
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = '{{ route("cotizacion.index") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Ocurrió un error al actualizar la cotización.',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud.',
                });
            });
    });
</script>
@endpush
@endsection