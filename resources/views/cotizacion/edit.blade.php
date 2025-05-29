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

                        <form method="POST" action="{{ route('cotizacion.update', $quote->quote_id) }}" id="edit-quote-form">
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
                                        <option value="{{ $customer->customers_id }}" data-discount="{{ $customer->customerType->discount_percentage ?? 0 }}" {{ old('customers_id', $quote->customers_id) == $customer->customers_id ? 'selected' : '' }}>
                                            {{ $customer->nit }} - {{ $customer->solicitante }} ({{ ucfirst($customer->tipo_cliente) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('customers_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>


                            <!-- Unidades -->
                            <h5>Unidades (Terrenos)</h5>
                            <div id="unit-container">
                                @php
                                    // Agrupar servicios/paquetes por unidad usando unit_index
                                    $quoteServices = $quote->quoteServices;
                                    $servicesPerUnit = [];
                                    foreach ($quoteServices as $qs) {
                                        $unitIdx = $qs->unit_index ?? 0;
                                        $servicesPerUnit[$unitIdx][] = $qs;
                                    }
                                    $unitCount = count($servicesPerUnit);
                                @endphp

                                @foreach (range(0, $unitCount - 1) as $unitIndex)
                                    <div class="unit-row mb-4 border p-3" data-index="{{ $unitIndex }}">
                                        <h6>Unidad {{ $unitIndex + 1 }}</h6>

                                        <!-- Servicios de la Unidad -->
                                        <h6>Servicios</h6>
                                        <div class="service-container">
                                            @php
                                                $unitServices = $servicesPerUnit[$unitIndex] ?? collect([]);
                                                $serviceIndex = 0;
                                            @endphp
                                            @foreach ($unitServices as $quoteService)
                                                @if ($quoteService->services_id && $quoteService->service)
                                                    <div class="service-row mb-3" data-service-index="{{ $serviceIndex }}">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label for="units_{{ $unitIndex }}_services_{{ $serviceIndex }}" class="form-label">Servicio</label>
                                                                <select class="form-control service-select" name="units[{{ $unitIndex }}][services][{{ $serviceIndex }}][service_id]" id="units_{{ $unitIndex }}_services_{{ $serviceIndex }}">
                                                                    <option value="">Seleccione un servicio</option>
                                                                    @foreach ($services as $service)
                                                                        <option value="{{ $service->services_id }}" data-price="{{ $service->precio }}" {{ $service->services_id == $quoteService->services_id ? 'selected' : '' }}>
                                                                            {{ $service->descripcion }} ({{ $service->precio }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label for="units_{{ $unitIndex }}_quantities_{{ $serviceIndex }}" class="form-label">Cantidad</label>
                                                                <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][services][{{ $serviceIndex }}][quantity]" id="units_{{ $unitIndex }}_quantities_{{ $serviceIndex }}" min="1" value="{{ $quoteService->cantidad }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Subtotal</label>
                                                                <input type="text" class="form-control subtotal" readonly value="{{ number_format($quoteService->subtotal, 2) }}">
                                                            </div>
                                                            <div class="col-md-2 d-flex align-items-end">
                                                                <button type="button" class="btn btn-danger delete-service-btn">Eliminar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $serviceIndex++;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @if ($serviceIndex == 0)
                                                <div class="service-row mb-3" data-service-index="0">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="units_{{ $unitIndex }}_services_0" class="form-label">Servicio</label>
                                                            <select class="form-control service-select" name="units[{{ $unitIndex }}][services][0][service_id]" id="units_{{ $unitIndex }}_services_0">
                                                                <option value="">Seleccione un servicio</option>
                                                                @foreach ($services as $service)
                                                                    <option value="{{ $service->services_id }}" data-price="{{ $service->precio }}">
                                                                        {{ $service->descripcion }} ({{ $service->precio }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="units_{{ $unitIndex }}_quantities_0" class="form-label">Cantidad</label>
                                                            <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][services][0][quantity]" id="units_{{ $unitIndex }}_quantities_0" min="1" value="1">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Subtotal</label>
                                                            <input type="text" class="form-control subtotal" readonly value="0">
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button" class="btn btn-danger delete-service-btn" style="display: none;">Eliminar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-secondary mb-3 add-service-btn">Agregar Servicio</button>

                                        <!-- Paquetes de la Unidad -->
                                        <h6>Paquetes de Servicios</h6>
                                        <div class="package-container">
                                            @php
                                                $packageIndex = 0;
                                            @endphp
                                            @foreach ($unitServices as $quoteService)
                                                @if ($quoteService->service_packages_id && $quoteService->servicePackage)
                                                    <div class="package-row mb-3" data-package-index="{{ $packageIndex }}">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label for="units_{{ $unitIndex }}_packages_{{ $packageIndex }}" class="form-label">Paquete</label>
                                                                <select class="form-control package-select" name="units[{{ $unitIndex }}][packages][{{ $packageIndex }}][package_id]" id="units_{{ $unitIndex }}_packages_{{ $packageIndex }}">
                                                                    <option value="">Seleccione un paquete</option>
                                                                    @foreach ($servicePackages as $servicePackage)
                                                                        <option value="{{ $servicePackage->service_packages_id }}" data-price="{{ $servicePackage->precio }}" {{ $servicePackage->service_packages_id == $quoteService->service_packages_id ? 'selected' : '' }}>
                                                                            {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label for="units_{{ $unitIndex }}_package_quantities_{{ $packageIndex }}" class="form-label">Cantidad</label>
                                                                <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][packages][{{ $packageIndex }}][quantity]" id="units_{{ $unitIndex }}_package_quantities_{{ $packageIndex }}" min="1" value="{{ $quoteService->cantidad }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Subtotal</label>
                                                                <input type="text" class="form-control subtotal" readonly value="{{ number_format($quoteService->subtotal, 2) }}">
                                                            </div>
                                                            <div class="col-md-2 d-flex align-items-end">
                                                                <button type="button" class="btn btn-danger delete-package-btn">Eliminar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $packageIndex++;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @if ($packageIndex == 0)
                                                <div class="package-row mb-3" data-package-index="0">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="units_{{ $unitIndex }}_packages_0" class="form-label">Paquete</label>
                                                            <select class="form-control package-select" name="units[{{ $unitIndex }}][packages][0][package_id]" id="units_{{ $unitIndex }}_packages_0">
                                                                <option value="">Seleccione un paquete</option>
                                                                @foreach ($servicePackages as $servicePackage)
                                                                    <option value="{{ $servicePackage->service_packages_id }}" data-price="{{ $servicePackage->precio }}">
                                                                        {{ $servicePackage->nombre }} ({{ $servicePackage->precio }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="units_{{ $unitIndex }}_package_quantities_0" class="form-label">Cantidad</label>
                                                            <input type="number" class="form-control quantity" name="units[{{ $unitIndex }}][packages][0][quantity]" id="units_{{ $unitIndex }}_package_quantities_0" min="1" value="1">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Subtotal</label>
                                                            <input type="text" class="form-control subtotal" readonly value="0">
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="button" class="btn btn-danger delete-package-btn" style="display: none;">Eliminar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-secondary mb-3 add-package-btn">Agregar Paquete</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-primary mb-3" id="add-unit-btn">Agregar Unidad</button>

                            <!-- Total -->
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
        <div id="services-data" style="display: none;" 
             data-services="{{ json_encode($services) }}"
             data-service-packages="{{ json_encode($servicePackages) }}"></div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const unitContainer = document.getElementById('unit-container');
                const addUnitBtn = document.getElementById('add-unit-btn');
                const totalDisplay = document.getElementById('total-amount');
                let unitIndex = {{ $unitCount }};
                let discountPercentage = 0;

                const servicesData = JSON.parse(document.getElementById('services-data').dataset.services);
                const servicePackages = JSON.parse(document.getElementById('services-data').dataset.servicePackages);

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

                // Calculate subtotal for a row
                function calculateSubtotal(row) {
                    const select = row.querySelector('.service-select, .package-select');
                    const quantity = row.querySelector('.quantity');
                    const subtotal = row.querySelector('.subtotal');

                    if (!select || !quantity || !subtotal) return 0;

                    const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
                    const qty = parseInt(quantity.value) || 1;
                    const subtotalValue = price * qty;
                    subtotal.value = subtotalValue.toFixed(2);
                    return subtotalValue;
                }

                // Calculate total
                function calculateTotal() {
                    let total = 0;
                    const unitRows = unitContainer.querySelectorAll('.unit-row');
                    unitRows.forEach(unitRow => {
                        const serviceRows = unitRow.querySelectorAll('.service-row');
                        const packageRows = unitRow.querySelectorAll('.package-row');
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
                    });
                    if (discountPercentage > 0) {
                        total = total * (1 - discountPercentage / 100);
                    }
                    totalDisplay.textContent = total.toFixed(2);
                }

                // Add new service row
                function addServiceRow(unitRow, unitIndex, serviceIndex) {
                    const serviceContainer = unitRow.querySelector('.service-container');
                    const newRow = document.createElement('div');
                    newRow.className = 'service-row mb-3';
                    newRow.dataset.serviceIndex = serviceIndex;
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <label for="units_${unitIndex}_services_${serviceIndex}" class="form-label">Servicio</label>
                                <select class="form-control service-select" name="units[${unitIndex}][services][${serviceIndex}][service_id]" id="units_${unitIndex}_services_${serviceIndex}">
                                    <option value="">Seleccione un servicio</option>
                                    ${servicesData.map(s => `<option value="${s.services_id}" data-price="${s.precio}">${s.descripcion} (${s.precio})</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="units_${unitIndex}_quantities_${serviceIndex}" class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity" name="units[${unitIndex}][services][${serviceIndex}][quantity]" id="units_${unitIndex}_quantities_${serviceIndex}" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal" readonly value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger delete-service-btn">Eliminar</button>
                            </div>
                        </div>
                    `;
                    serviceContainer.appendChild(newRow);
                    const select = newRow.querySelector('.service-select');
                    const quantity = newRow.querySelector('.quantity');
                    const deleteBtn = newRow.querySelector('.delete-service-btn');
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
                    });
                    calculateSubtotal(newRow);
                    calculateTotal();
                }

                // Add new package row
                function addPackageRow(unitRow, unitIndex, packageIndex) {
                    const packageContainer = unitRow.querySelector('.package-container');
                    const newRow = document.createElement('div');
                    newRow.className = 'package-row mb-3';
                    newRow.dataset.packageIndex = packageIndex;
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-4">
                                <label for="units_${unitIndex}_packages_${packageIndex}" class="form-label">Paquete</label>
                                <select class="form-control package-select" name="units[${unitIndex}][packages][${packageIndex}][package_id]" id="units_${unitIndex}_packages_${packageIndex}">
                                    <option value="">Seleccione un paquete</option>
                                    ${servicePackages.map(p => `<option value="${p.service_packages_id}" data-price="${p.precio}">${p.nombre} (${p.precio})</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="units_${unitIndex}_package_quantities_${packageIndex}" class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity" name="units[${unitIndex}][packages][${packageIndex}][quantity]" id="units_${unitIndex}_package_quantities_${packageIndex}" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control subtotal" readonly value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger delete-package-btn">Eliminar</button>
                            </div>
                        </div>
                    `;
                    packageContainer.appendChild(newRow);
                    const select = newRow.querySelector('.package-select');
                    const quantity = newRow.querySelector('.quantity');
                    const deleteBtn = newRow.querySelector('.delete-package-btn');
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
                    });
                    calculateSubtotal(newRow);
                    calculateTotal();
                }

                // Add new unit
                addUnitBtn.addEventListener('click', () => {
                    const newUnitRow = document.createElement('div');
                    newUnitRow.className = 'unit-row mb-4 border p-3';
                    newUnitRow.dataset.index = unitIndex;
                    newUnitRow.innerHTML = `
                        <h6>Unidad ${unitIndex + 1}</h6>
                        <h6>Servicios</h6>
                        <div class="service-container">
                            <div class="service-row mb-3" data-service-index="0">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="units_${unitIndex}_services_0" class="form-label">Servicio</label>
                                        <select class="form-control service-select" name="units[${unitIndex}][services][0][service_id]" id="units_${unitIndex}_services_0">
                                            <option value="">Seleccione un servicio</option>
                                            ${servicesData.map(s => `<option value="${s.services_id}" data-price="${s.precio}">${s.descripcion} (${s.precio})</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="units_${unitIndex}_quantities_0" class="form-label">Cantidad</label>
                                        <input type="number" class="form-control quantity" name="units[${unitIndex}][services][0][quantity]" id="units_${unitIndex}_quantities_0" min="1" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly value="0">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger delete-service-btn" style="display: none;">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mb-3 add-service-btn">Agregar Servicio</button>
                        <h6>Paquetes de Servicios</h6>
                        <div class="package-container">
                            <div class="package-row mb-3" data-package-index="0">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="units_${unitIndex}_packages_0" class="form-label">Paquete</label>
                                        <select class="form-control package-select" name="units[${unitIndex}][packages][0][package_id]" id="units_${unitIndex}_packages_0">
                                            <option value="">Seleccione un paquete</option>
                                            ${servicePackages.map(p => `<option value="${p.service_packages_id}" data-price="${p.precio}">${p.nombre} (${p.precio})</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="units_${unitIndex}_package_quantities_0" class="form-label">Cantidad</label>
                                        <input type="number" class="form-control quantity" name="units[${unitIndex}][packages][0][quantity]" id="units_${unitIndex}_package_quantities_0" min="1" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly value="0">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger delete-package-btn" style="display: none;">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mb-3 add-package-btn">Agregar Paquete</button>
                    `;
                    unitContainer.appendChild(newUnitRow);

                    // Initialize service and package buttons for the new unit
                    const addServiceBtn = newUnitRow.querySelector('.add-service-btn');
                    const addPackageBtn = newUnitRow.querySelector('.add-package-btn');
                    let serviceIndex = 1;
                    let packageIndex = 1;

                    addServiceBtn.addEventListener('click', () => {
                        addServiceRow(newUnitRow, unitIndex, serviceIndex);
                        serviceIndex++;
                    });

                    addPackageBtn.addEventListener('click', () => {
                        addPackageRow(newUnitRow, unitIndex, packageIndex);
                        packageIndex++;
                    });

                    // Initialize first service and package rows
                    const serviceRow = newUnitRow.querySelector('.service-row');
                    const packageRow = newUnitRow.querySelector('.package-row');
                    const serviceSelect = serviceRow.querySelector('.service-select');
                    const serviceQuantity = serviceRow.querySelector('.quantity');
                    const packageSelect = packageRow.querySelector('.package-select');
                    const packageQuantity = packageRow.querySelector('.quantity');

                    serviceSelect.addEventListener('change', () => {
                        calculateSubtotal(serviceRow);
                        calculateTotal();
                    });
                    serviceQuantity.addEventListener('input', () => {
                        calculateSubtotal(serviceRow);
                        calculateTotal();
                    });
                    packageSelect.addEventListener('change', () => {
                        calculateSubtotal(packageRow);
                        calculateTotal();
                    });
                    packageQuantity.addEventListener('input', () => {
                        calculateSubtotal(packageRow);
                        calculateTotal();
                    });

                    unitIndex++;
                    calculateTotal();
                });

                // Initialize existing units
                const unitRows = unitContainer.querySelectorAll('.unit-row');
                unitRows.forEach((unitRow, idx) => {
                    const addServiceBtn = unitRow.querySelector('.add-service-btn');
                    const addPackageBtn = unitRow.querySelector('.add-package-btn');
                    let serviceIndex = unitRow.querySelectorAll('.service-row').length;
                    let packageIndex = unitRow.querySelectorAll('.package-row').length;

                    addServiceBtn.addEventListener('click', () => {
                        addServiceRow(unitRow, idx, serviceIndex);
                        serviceIndex++;
                    });

                    addPackageBtn.addEventListener('click', () => {
                        addPackageRow(unitRow, idx, packageIndex);
                        packageIndex++;
                    });

                    const serviceRows = unitRow.querySelectorAll('.service-row');
                    const packageRows = unitRow.querySelectorAll('.package-row');

                    serviceRows.forEach(row => {
                        const select = row.querySelector('.service-select');
                        const quantity = row.querySelector('.quantity');
                        const deleteBtn = row.querySelector('.delete-service-btn');
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
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', () => {
                                row.remove();
                                calculateTotal();
                            });
                        }
                    });

                    packageRows.forEach(row => {
                        const select = row.querySelector('.package-select');
                        const quantity = row.querySelector('.quantity');
                        const deleteBtn = row.querySelector('.delete-package-btn');
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
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', () => {
                                row.remove();
                                calculateTotal();
                            });
                        }
                    });
                });

                // Initial total calculation
                calculateTotal();

                // Form submission
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
            });
        </script>
    @endpush
@endsection