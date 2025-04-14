@extends('layouts.master')

@section('contenido')
    <center>
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%">
    </center>

    <div class="card-body">
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: "{{ session('error') }}",
                    confirmButtonText: 'Understood'
                });
            </script>
        @endif

        <br><br>

        <center>
            <div class="container">
                <div class="card">
                    <h5 class="card-header">Editar Cotización {{ $quote->quote_id }}</h5>
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

                        <form method="POST" action="{{ route('cotizacion.update', $quote->quote_id) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="quote_id" class="form-label">ID de la Cotización</label>
                                <input type="text" class="form-control" id="quote_id" name="quote_id" value="{{ old('quote_id', $quote->quote_id) }}" required>
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

                            <!-- Servicios -->
                            <h5>Servicios</h5>
                            <div id="services-container">
                                @foreach($quote->services as $index => $service)
                                    <div class="service-row mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="services" class="form-label">Servicio</label>
                                                <select class="form-control service-select" name="services[]" required>
                                                    <option value="">Seleccione un servicio</option>
                                                    @foreach ($services as $s)
                                                        <option value="{{ $s->services_id }}" data-precio="{{ $s->precio }}" {{ $s->services_id == $service->services_id ? 'selected' : '' }}>
                                                            {{ $s->descripcion }} ({{ $s->precio }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="quantities" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control quantity-input" name="quantities[]" min="1" value="{{ $service->pivot->cantidad }}" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-service-btn" style="display: none;">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($quote->services->isEmpty())
                                    <div class="service-row mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="services" class="form-label">Servicio</label>
                                                <select class="form-control service-select" name="services[]" required>
                                                    <option value="">Seleccione un servicio</option>
                                                    @foreach ($services as $service)
                                                        <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">
                                                            {{ $service->descripcion }} ({{ $service->precio }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="quantities" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control quantity-input" name="quantities[]" min="1" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-service-btn" style="display: none;">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-service-btn">Agregar Otro Servicio</button>

                            <!-- Paquetes de Servicios -->
                            <h5>Paquetes de Servicios</h5>
                            <div id="packages-container">
                                @foreach($quote->servicePackages as $index => $package)
                                    <div class="package-row mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="service_packages" class="form-label">Paquete</label>
                                                <select class="form-control package-select" name="service_packages[]" required>
                                                    <option value="">Seleccione un paquete</option>
                                                    @foreach ($servicePackages as $p)
                                                        <option value="{{ $p->service_packages_id }}" data-precio="{{ $p->precio }}" {{ $p->service_packages_id == $package->service_packages_id ? 'selected' : '' }}>
                                                            {{ $p->nombre }} ({{ $p->precio }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="package_quantities" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control package-quantity-input" name="package_quantities[]" min="1" value="{{ $package->pivot->cantidad }}" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-package-btn" style="display: none;">Eliminar</button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <p><strong>Servicios Incluidos:</strong></p>
                                                <ul class="package-services-list">
                                                    @foreach($package->included_services ?? [] as $serviceDesc)
                                                        <li>{{ $serviceDesc }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($quote->servicePackages->isEmpty())
                                    <div class="package-row mb-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="service_packages" class="form-label">Paquete</label>
                                                <select class="form-control package-select" name="service_packages[]" required>
                                                    <option value="">Seleccione un paquete</option>
                                                    @foreach ($servicePackages as $servicePackage)
                                                        <option value="{{ $servicePackage->service_packages_id }}" data-precio="{{ $servicePackage->precio }}">
                                                            {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="package_quantities" class="form-label">Cantidad</label>
                                                <input type="number" class="form-control package-quantity-input" name="package_quantities[]" min="1" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-package-btn" style="display: none;">Eliminar</button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <p><strong>Servicios Incluidos:</strong></p>
                                                <ul class="package-services-list"></ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-package-btn">Agregar Otro Paquete</button>

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
        <!-- SweetAlert2 para mensajes de error -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Manejo de servicios
                const servicesContainer = document.getElementById('services-container');
                const addServiceButton = document.getElementById('add-service-btn');

                addServiceButton.addEventListener('click', function() {
                    const newRow = document.createElement('div');
                    newRow.classList.add('service-row', 'mb-3');
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <label for="services" class="form-label">Servicio</label>
                                <select class="form-control service-select" name="services[]" required>
                                    <option value="">Seleccione un servicio</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">
                                            {{ $service->descripcion }} ({{ $service->precio }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="quantities" class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity-input" name="quantities[]" min="1" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-service-btn">Eliminar</button>
                            </div>
                        </div>
                    `;
                    servicesContainer.appendChild(newRow);
                    updateServiceRemoveButtons();
                });

                servicesContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-service-btn')) {
                        e.target.closest('.service-row').remove();
                        updateServiceRemoveButtons();
                    }
                });

                function updateServiceRemoveButtons() {
                    const rows = servicesContainer.querySelectorAll('.service-row');
                    rows.forEach((row, index) => {
                        const removeBtn = row.querySelector('.remove-service-btn');
                        removeBtn.style.display = index === 0 && rows.length === 1 ? 'none' : 'block';
                    });
                }

                // Manejo de paquetes
                const packagesContainer = document.getElementById('packages-container');
                const addPackageButton = document.getElementById('add-package-btn');
                const packagesData = JSON.parse('{{ json_encode($servicePackages->mapWithKeys(function ($package) {
                    return [$package->service_packages_id => $package->included_services ?? []];
                })) }}');

                addPackageButton.addEventListener('click', function() {
                    const newRow = document.createElement('div');
                    newRow.classList.add('package-row', 'mb-3');
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <label for="service_packages" class="form-label">Paquete</label>
                                <select class="form-control package-select" name="service_packages[]" required>
                                    <option value="">Seleccione un paquete</option>
                                    @foreach ($servicePackages as $servicePackage)
                                        <option value="{{ $servicePackage->service_packages_id }}" data-precio="{{ $servicePackage->precio }}">
                                            {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="package_quantities" class="form-label">Cantidad</label>
                                <input type="number" class="form-control package-quantity-input" name="package_quantities[]" min="1" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-package-btn">Eliminar</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <p><strong>Servicios Incluidos:</strong></p>
                                <ul class="package-services-list"></ul>
                            </div>
                        </div>
                    `;
                    packagesContainer.appendChild(newRow);
                    updatePackageRemoveButtons();
                    attachPackageChangeListener(newRow.querySelector('.package-select'));
                });

                packagesContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-package-btn')) {
                        e.target.closest('.package-row').remove();
                        updatePackageRemoveButtons();
                    }
                });

                function updatePackageRemoveButtons() {
                    const rows = packagesContainer.querySelectorAll('.package-row');
                    rows.forEach((row, index) => {
                        const removeBtn = row.querySelector('.remove-package-btn');
                        removeBtn.style.display = index === 0 && rows.length === 1 ? 'none' : 'block';
                    });
                }

                function attachPackageChangeListener(select) {
                    select.addEventListener('change', function() {
                        const packageId = this.value;
                        const servicesList = this.closest('.package-row').querySelector('.package-services-list');
                        servicesList.innerHTML = '';

                        if (packageId) {
                            const services = packagesData[packageId] || [];
                            services.forEach(service => {
                                const li = document.createElement('li');
                                li.textContent = service;
                                servicesList.appendChild(li);
                            });
                        }
                    });
                }

                // Inicializar listeners para los selects de paquetes existentes
                document.querySelectorAll('.package-select').forEach(select => {
                    attachPackageChangeListener(select);
                    select.dispatchEvent(new Event('change')); // Disparar evento para cargar servicios incluidos iniciales
                });

                // Disparar eventos iniciales
                updateServiceRemoveButtons();
                updatePackageRemoveButtons();
            });
        </script>
    @endpush
@endsection