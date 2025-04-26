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
                    <h5 class="card-header">Crear Nueva Cotización</h5>
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

                        <form method="POST" action="{{ route('cotizacion.store') }}" id="create-quote-form">
                            @csrf
                            <div class="mb-3">
                                <label for="quote_id" class="form-label">ID de la Cotización</label>
                                <input type="text" class="form-control" id="quote_id" name="quote_id" value="{{ old('quote_id') }}" required>
                                @error('quote_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="customers_id" class="form-label">Cliente (NIT)</label>
                                <select class="form-control" id="customers_id" name="customers_id" required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->customers_id }}" data-discount="{{ $customer->customerType->discount_percentage ?? 0 }}" {{ old('customers_id') == $customer->customers_id ? 'selected' : '' }}>
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
                                <div class="service-row mb-3" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="services_0" class="form-label">Servicio</label>
                                            <select class="form-control service-select" name="services[0]" id="services_0">
                                                <option value="">Seleccione un servicio</option>
                                                @foreach ($services as $service)
                                                    <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">
                                                        {{ $service->descripcion }} ({{ $service->precio }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="quantities_0" class="form-label">Cantidad</label>
                                            <input type="number" class="form-control quantity-input" name="quantities[0]" id="quantities_0" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control subtotal-input" readonly value="0">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-service-btn" style="display: none;">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-service-btn">Agregar Otro Servicio</button>

                            <!-- Paquetes de Servicios -->
                            <h5>Paquetes de Servicios</h5>
                            <div id="packages-container">
                                <div class="package-row mb-3" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="service_packages_0" class="form-label">Paquete</label>
                                            <select class="form-control package-select" name="service_packages[0]" id="service_packages_0">
                                                <option value="">Seleccione un paquete</option>
                                                @foreach ($servicePackages as $servicePackage)
                                                    <option value="{{ $servicePackage->service_packages_id }}" data-precio="{{ $servicePackage->precio }}">
                                                        {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="package_quantities_0" class="form-label">Cantidad</label>
                                            <input type="number" class="form-control package-quantity-input" name="package_quantities[0]" id="package_quantities_0" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control subtotal-input" readonly value="0">
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
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-package-btn">Agregar Otro Paquete</button>

                            <!-- Total -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5>Total: <span id="total-amount">0</span></h5>
                                </div>
                            </div>

                            <br>
                            <button type="submit" class="btn btn-primary">Crear Cotización</button>
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
                        $includedServices = $package->included_services ?? [];
                        if (is_string($includedServices)) {
                            $includedServices = json_decode($includedServices, true) ?? [];
                        }
                        return [$package->service_packages_id => $includedServices];
                    })->toArray();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al calcular packagesData: ' . $e->getMessage());
                $packagesData = [];
            }
        @endphp
        <div id="services-data" style="display: none;" 
             data-services="{{ json_encode($services) }}"
             data-service-packages="{{ json_encode($servicePackages) }}"
             data-packages-data="{{ json_encode($packagesData) }}"></div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const servicesContainer = document.getElementById('services-container');
                const packagesContainer = document.getElementById('packages-container');
                const addServiceBtn = document.getElementById('add-service-btn');
                const addPackageBtn = document.getElementById('add-package-btn');
                const totalAmountSpan = document.getElementById('total-amount');
                let serviceIndex = 1;
                let packageIndex = 1;
                let discountPercentage = 0;

                // Update discount percentage when customer is selected
                document.getElementById('customers_id').addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    discountPercentage = parseFloat(selectedOption.dataset.discount || 0);
                    calculateTotal();
                });

                // Initialize discount percentage on page load
                const customerSelect = document.getElementById('customers_id');
                if (customerSelect.selectedIndex > 0) {
                    discountPercentage = parseFloat(customerSelect.options[customerSelect.selectedIndex].dataset.discount || 0);
                }

                // Function to calculate subtotal for a row
                function calculateSubtotal(row) {
                    const select = row.querySelector('.service-select, .package-select');
                    const quantityInput = row.querySelector('.quantity-input, .package-quantity-input');
                    const subtotalInput = row.querySelector('.subtotal-input');
                    const price = parseFloat(select.options[select.selectedIndex]?.dataset.precio || 0);
                    const quantity = parseInt(quantityInput.value || 1);
                    const subtotal = price * quantity;
                    subtotalInput.value = subtotal.toFixed(2);
                    return subtotal;
                }

                // Function to calculate total
                function calculateTotal() {
                    let total = 0;
                    const serviceRows = servicesContainer.querySelectorAll('.service-row');
                    const packageRows = packagesContainer.querySelectorAll('.package-row');

                    serviceRows.forEach(row => {
                        const select = row.querySelector('.service-select');
                        if (select.value) {
                            total += calculateSubtotal(row);
                        }
                    });

                    packageRows.forEach(row => {
                        const select = row.querySelector('.package-select');
                        if (select.value) {
                            total += calculateSubtotal(row);
                        }
                    });

                    // Apply discount
                    if (discountPercentage > 0) {
                        total = total * (1 - discountPercentage / 100);
                    }

                    totalAmountSpan.textContent = total.toFixed(2);
                }

                // Event delegation for dynamic rows (services)
                servicesContainer.addEventListener('change', function (e) {
                    if (e.target.classList.contains('service-select') || e.target.classList.contains('quantity-input')) {
                        calculateTotal();
                    }
                });

                // Event delegation for dynamic rows (packages)
                packagesContainer.addEventListener('change', function (e) {
                    if (e.target.classList.contains('package-select') || e.target.classList.contains('package-quantity-input')) {
                        calculateTotal();
                        // Update included services list when package changes
                        const packageRow = e.target.closest('.package-row');
                        updatePackageServicesList(packageRow);
                    }
                });

                // Add new service row
                addServiceBtn.addEventListener('click', function () {
                    const newRow = servicesContainer.querySelector('.service-row').cloneNode(true);
                    newRow.dataset.index = serviceIndex;
                    newRow.querySelector('.service-select').name = `services[${serviceIndex}]`;
                    newRow.querySelector('.service-select').id = `services_${serviceIndex}`;
                    newRow.querySelector('.service-select').value = '';
                    newRow.querySelector('.quantity-input').name = `quantities[${serviceIndex}]`;
                    newRow.querySelector('.quantity-input').id = `quantities_${serviceIndex}`;
                    newRow.querySelector('.quantity-input').value = '1';
                    newRow.querySelector('.subtotal-input').value = '0';
                    const removeBtn = newRow.querySelector('.remove-service-btn');
                    removeBtn.style.display = 'block';
                    servicesContainer.appendChild(newRow);
                    updateRemoveButtons(servicesContainer, '.service-row', '.remove-service-btn');
                    serviceIndex++;
                    calculateTotal();
                });

                // Add new package row
                addPackageBtn.addEventListener('click', function () {
                    const newRow = packagesContainer.querySelector('.package-row').cloneNode(true);
                    newRow.dataset.index = packageIndex;
                    newRow.querySelector('.package-select').name = `service_packages[${packageIndex}]`;
                    newRow.querySelector('.package-select').id = `service_packages_${packageIndex}`;
                    newRow.querySelector('.package-select').value = '';
                    newRow.querySelector('.package-quantity-input').name = `package_quantities[${packageIndex}]`;
                    newRow.querySelector('.package-quantity-input').id = `package_quantities_${packageIndex}`;
                    newRow.querySelector('.package-quantity-input').value = '1';
                    newRow.querySelector('.subtotal-input').value = '0';
                    newRow.querySelector('.package-services-list').innerHTML = '';
                    const removeBtn = newRow.querySelector('.remove-package-btn');
                    removeBtn.style.display = 'block';
                    packagesContainer.appendChild(newRow);
                    updateRemoveButtons(packagesContainer, '.package-row', '.remove-package-btn');
                    packageIndex++;
                    calculateTotal();
                });

                // Remove service or package row
                function updateRemoveButtons(container, rowSelector, btnSelector) {
                    const rows = container.querySelectorAll(rowSelector);
                    rows.forEach((row, index) => {
                        const removeBtn = row.querySelector(btnSelector);
                        removeBtn.style.display = index === 0 ? 'none' : 'block';
                        removeBtn.onclick = () => {
                            row.remove();
                            calculateTotal();
                            updateRemoveButtons(container, rowSelector, btnSelector);
                        };
                    });
                }

                // Update included services list for a package
                function updatePackageServicesList(packageRow) {
                    const packageSelect = packageRow.querySelector('.package-select');
                    const servicesList = packageRow.querySelector('.package-services-list');
                    servicesList.innerHTML = '';

                    if (packageSelect.value) {
                        const packageId = packageSelect.value;
                        const packagesData = JSON.parse(document.getElementById('services-data').dataset.packagesData);
                        const servicesData = JSON.parse(document.getElementById('services-data').dataset.services);
                        const includedServiceIds = packagesData[packageId] || [];

                        includedServiceIds.forEach(serviceId => {
                            const service = servicesData.find(s => s.services_id == serviceId);
                            if (service) {
                                const li = document.createElement('li');
                                li.textContent = service.descripcion;
                                servicesList.appendChild(li);
                            }
                        });
                    }
                }

                // Initialize remove buttons
                updateRemoveButtons(servicesContainer, '.service-row', '.remove-service-btn');
                updateRemoveButtons(packagesContainer, '.package-row', '.remove-package-btn');

                // Initial calculation
                calculateTotal();
            });
        </script>
    @endpush
@endsection