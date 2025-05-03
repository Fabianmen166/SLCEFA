@extends('layouts.master')

@section('contenido')
    <center>
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%" alt="Logo Agrosoft">
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
                            <div id="service-container">
                                <div class="service-row mb-3" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="services_0" class="form-label">Servicio</label>
                                            <select class="form-control service-select" name="services[0]" id="services_0">
                                                <option value="">Seleccione un servicio</option>
                                                @foreach ($services as $service)
                                                    <option value="{{ $service->services_id }}" data-price="{{ $service->precio }}">
                                                        {{ $service->descripcion }} ({{ $service->precio }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="quantities_0" class="form-label">Cantidad</label>
                                            <input type="number" class="form-control quantity" name="quantities[0]" id="quantities_0" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control subtotal" readonly value="0">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger delete-btn" style="display: none;">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-service-btn">Agregar Otro Servicio</button>

                            <!-- Paquetes de Servicios -->
                            <h5>Paquetes de Servicios</h5>
                            <div id="package-container">
                                <div class="package-row mb-3" data-index="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="service_packages_0" class="form-label">Paquete</label>
                                            <select class="form-control package-select" name="service_packages[0]" id="service_packages_0">
                                                <option value="">Seleccione un paquete</option>
                                                @foreach ($servicePackages as $servicePackage)
                                                    <option value="{{ $servicePackage->service_packages_id }}" data-price="{{ $servicePackage->precio }}">
                                                        {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="package_quantities_0" class="form-label">Cantidad</label>
                                            <input type="number" class="form-control quantity" name="package_quantities[0]" id="package_quantities_0" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Subtotal</label>
                                            <input type="text" class="form-control subtotal" readonly value="0">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger delete-btn" style="display: none;">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-package-btn">Agregar Otro Paquete</button>

                            <!-- Total -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5>Total: <span id="total-amount">0.00</span></h5>
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
        <div id="services-data" style="display: none;" 
             data-services="{{ json_encode($services) }}"
             data-service-packages="{{ json_encode($servicePackages) }}"></div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const serviceContainer = document.getElementById('service-container');
                const packageContainer = document.getElementById('package-container');
                const addServiceBtn = document.getElementById('add-service-btn');
                const addPackageBtn = document.getElementById('add-package-btn');
                const totalDisplay = document.getElementById('total-amount');
                let serviceIndex = 1; // Starting at 1 to account for initial row
                let packageIndex = 1; // Starting at 1 to account for initial row
                let discountPercentage = 0;

                // Parse data from hidden element
                const servicesData = JSON.parse(document.getElementById('services-data').dataset.services);
                const servicePackages = JSON.parse(document.getElementById('services-data').dataset.servicePackages);
                console.log('Services Data:', servicesData);
                console.log('Service Packages:', servicePackages);

                // Update discount percentage when customer is selected
                document.getElementById('customers_id').addEventListener('change', (e) => {
                    const selectedOption = e.target.selectedOptions[0];
                    discountPercentage = parseFloat(selectedOption.dataset.discount || 0);
                    calculateTotal();
                });

                // Initialize discount percentage
                const customerSelect = document.getElementById('customers_id');
                if (customerSelect.selectedIndex > 0) {
                    discountPercentage = parseFloat(customerSelect.options[customerSelect.selectedIndex].dataset.discount || 0);
                }

                // Function to calculate subtotal for a row (without calling updateTotal)
                function calculateSubtotal(row) {
                    const select = row.querySelector('.service-select, .package-select');
                    const quantity = row.querySelector('.quantity');
                    const subtotal = row.querySelector('.subtotal');

                    if (!select || !quantity || !subtotal) {
                        console.error('Missing elements in row:', row);
                        return 0;
                    }

                    const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
                    const qty = parseInt(quantity.value) || 1;
                    const subtotalValue = price * qty;
                    subtotal.value = subtotalValue.toFixed(2);
                    return subtotalValue;
                }

                // Function to calculate total (standalone, no recursive calls)
                function calculateTotal() {
                    let total = 0;
                    const serviceRows = serviceContainer.querySelectorAll('.service-row');
                    const packageRows = packageContainer.querySelectorAll('.package-row');

                    serviceRows.forEach(row => {
                        if (row.querySelector('.service-select')?.value) {
                            total += calculateSubtotal(row);
                        }
                    });

                    packageRows.forEach(row => {
                        if (row.querySelector('.package-select')?.value) {
                            total += calculateSubtotal(row);
                        }
                    });

                    if (discountPercentage > 0) {
                        total = total * (1 - discountPercentage / 100);
                    }
                    totalDisplay.textContent = total.toFixed(2);
                }

                // Add new service row
                addServiceBtn.addEventListener('click', () => {
                    const newRow = document.createElement('div');
                    newRow.className = 'service-row mb-3';
                    newRow.dataset.index = serviceIndex;
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <label for="services_${serviceIndex}" class="form-label">Servicio</label>
                                <select class="form-control service-select" name="services[${serviceIndex}]" id="services_${serviceIndex}">
                                    <option value="">Seleccione un servicio</option>
                                    ${servicesData.map(s => `<option value="${s.services_id}" data-price="${s.precio}">${s.descripcion} (${s.precio})</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="quantities_${serviceIndex}" class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity" name="quantities[${serviceIndex}]" id="quantities_${serviceIndex}" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal" readonly value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                            </div>
                        </div>
                    `;
                    serviceContainer.appendChild(newRow);
                    const select = newRow.querySelector('.service-select');
                    const quantity = newRow.querySelector('.quantity');
                    const deleteBtn = newRow.querySelector('.delete-btn');
                    select.addEventListener('change', () => {
                        calculateSubtotal(newRow);
                        calculateTotal();
                    });
                    quantity.addEventListener('input', () => {
                        calculateSubtotal(newRow);
                        calculateTotal();
                    });
                    deleteBtn.addEventListener('click', () => {
                        newRow.remove();
                        calculateTotal();
                        serviceIndex--;
                    });
                    serviceIndex++;
                    calculateSubtotal(newRow);
                    calculateTotal();
                    console.log('Botón Agregar Otro Servicio clicado');
                });

                // Add new package row
                addPackageBtn.addEventListener('click', () => {
                    const newRow = document.createElement('div');
                    newRow.className = 'package-row mb-3';
                    newRow.dataset.index = packageIndex;
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <label for="service_packages_${packageIndex}" class="form-label">Paquete</label>
                                <select class="form-control package-select" name="service_packages[${packageIndex}]" id="service_packages_${packageIndex}">
                                    <option value="">Seleccione un paquete</option>
                                    ${servicePackages.map(p => `<option value="${p.service_packages_id}" data-price="${p.precio}">${p.nombre} (${p.precio})</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="package_quantities_${packageIndex}" class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity" name="package_quantities[${packageIndex}]" id="package_quantities_${packageIndex}" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal" readonly value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                            </div>
                        </div>
                    `;
                    packageContainer.appendChild(newRow);
                    const select = newRow.querySelector('.package-select');
                    const quantity = newRow.querySelector('.quantity');
                    const deleteBtn = newRow.querySelector('.delete-btn');
                    select.addEventListener('change', () => {
                        calculateSubtotal(newRow);
                        calculateTotal();
                    });
                    quantity.addEventListener('input', () => {
                        calculateSubtotal(newRow);
                        calculateTotal();
                    });
                    deleteBtn.addEventListener('click', () => {
                        newRow.remove();
                        calculateTotal();
                        packageIndex--;
                    });
                    packageIndex++;
                    calculateSubtotal(newRow);
                    calculateTotal();
                    console.log('Botón Agregar Otro Paquete clicado');
                });

                // Attach change listeners to initial rows
                const initialServiceRows = serviceContainer.querySelectorAll('.service-row');
                initialServiceRows.forEach(row => {
                    const select = row.querySelector('.service-select');
                    const quantity = row.querySelector('.quantity');
                    if (select && quantity) {
                        select.addEventListener('change', () => {
                            calculateSubtotal(row);
                            calculateTotal();
                        });
                        quantity.addEventListener('input', () => {
                            calculateSubtotal(row);
                            calculateTotal();
                        });
                    }
                });

                const initialPackageRows = packageContainer.querySelectorAll('.package-row');
                initialPackageRows.forEach(row => {
                    const select = row.querySelector('.package-select');
                    const quantity = row.querySelector('.quantity');
                    if (select && quantity) {
                        select.addEventListener('change', () => {
                            calculateSubtotal(row);
                            calculateTotal();
                        });
                        quantity.addEventListener('input', () => {
                            calculateSubtotal(row);
                            calculateTotal();
                        });
                    }
                });

                // Initial total calculation
                calculateTotal();
            });
        </script>
    @endpush
@endsection