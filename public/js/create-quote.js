// public/js/create-quote.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('create-quote.js cargado correctamente');

    // Leer datos desde los atributos data-*
    const dataElement = document.getElementById('services-data');
    if (!dataElement) {
        console.error('Elemento #services-data no encontrado');
        return;
    }

    const services = JSON.parse(dataElement.getAttribute('data-services')) || [];
    const servicePackages = JSON.parse(dataElement.getAttribute('data-service-packages')) || [];
    const packagesData = JSON.parse(dataElement.getAttribute('data-packages-data')) || {};

    console.log('services:', services);
    console.log('servicePackages:', servicePackages);
    console.log('packagesData:', packagesData);

    // Manejo de servicios
    const servicesContainer = document.getElementById('services-container');
    const addServiceButton = document.getElementById('add-service-btn');
    let serviceIndex = document.querySelectorAll('.service-row').length || 0;

    if (!servicesContainer) {
        console.error('Contenedor #services-container no encontrado');
        return;
    }
    if (!addServiceButton) {
        console.error('Botón #add-service-btn no encontrado');
        return;
    }

    // Remover eventos previos para evitar duplicados
    const newAddServiceButton = addServiceButton.cloneNode(true);
    addServiceButton.parentNode.replaceChild(newAddServiceButton, addServiceButton);

    newAddServiceButton.addEventListener('click', function() {
        console.log('Botón Agregar Otro Servicio clicado');
        serviceIndex++;
        const newRow = document.createElement('div');
        newRow.classList.add('service-row', 'mb-3');
        newRow.setAttribute('data-index', serviceIndex);

        const selectOptions = services.map(service => 
            `<option value="${service.services_id}" data-precio="${service.precio}">${service.descripcion} (${service.precio})</option>`
        ).join('');

        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="services_${serviceIndex}" class="form-label">Servicio</label>
                    <select class="form-control service-select" name="services[${serviceIndex}]" id="services_${serviceIndex}">
                        <option value="">Seleccione un servicio</option>
                        ${selectOptions}
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="quantities_${serviceIndex}" class="form-label">Cantidad</label>
                    <input type="number" class="form-control quantity-input" name="quantities[${serviceIndex}]" id="quantities_${serviceIndex}" min="1">
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
            console.log('Botón Eliminar Servicio clicado');
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
    let packageIndex = document.querySelectorAll('.package-row').length || 0;

    if (!packagesContainer) {
        console.error('Contenedor #packages-container no encontrado');
        return;
    }
    if (!addPackageButton) {
        console.error('Botón #add-package-btn no encontrado');
        return;
    }

    // Remover eventos previos para evitar duplicados
    const newAddPackageButton = addPackageButton.cloneNode(true);
    addPackageButton.parentNode.replaceChild(newAddPackageButton, addPackageButton);

    newAddPackageButton.addEventListener('click', function() {
        console.log('Botón Agregar Otro Paquete clicado');
        packageIndex++;
        const newRow = document.createElement('div');
        newRow.classList.add('package-row', 'mb-3');
        newRow.setAttribute('data-index', packageIndex);

        const selectOptions = servicePackages.map(pkg => 
            `<option value="${pkg.service_packages_id}" data-precio="${pkg.precio}">${pkg.nombre} (${pkg.precio})</option>`
        ).join('');

        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <label for="service_packages_${packageIndex}" class="form-label">Paquete</label>
                    <select class="form-control package-select" name="service_packages[${packageIndex}]" id="service_packages_${packageIndex}">
                        <option value="">Seleccione un paquete</option>
                        ${selectOptions}
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="package_quantities_${packageIndex}" class="form-label">Cantidad</label>
                    <input type="number" class="form-control package-quantity-input" name="package_quantities[${packageIndex}]" id="package_quantities_${packageIndex}" min="1">
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
            console.log('Botón Eliminar Paquete clicado');
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

            console.log(`Paquete seleccionado: ${packageId}, Servicios:`, packagesData[packageId]);

            if (packageId && packagesData[packageId]) {
                let includedServices = packagesData[packageId];
                
                if (typeof includedServices === 'string') {
                    try {
                        includedServices = JSON.parse(includedServices);
                    } catch (e) {
                        console.error('Error al parsear included_services:', e);
                        includedServices = [];
                    }
                }

                if (Array.isArray(includedServices) && includedServices.length > 0) {
                    includedServices.forEach(service => {
                        const li = document.createElement('li');
                        li.textContent = service;
                        servicesList.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = 'No hay servicios incluidos en este paquete.';
                    servicesList.appendChild(li);
                }
            }
        });
    }

    document.querySelectorAll('.package-select').forEach(select => {
        attachPackageChangeListener(select);
        console.log('Listener de change añadido a select:', select.id);
    });

    // Inicializar los botones de eliminación
    updateServiceRemoveButtons();
    updatePackageRemoveButtons();
});