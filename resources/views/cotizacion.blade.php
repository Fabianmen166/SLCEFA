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

                        <form method="POST" action="{{ route('cotizacion.store') }}">
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
                                        <option value="{{ $customer->customers_id }}">{{ $customer->nit }} - {{ $customer->solicitante }} ({{ ucfirst($customer->tipo_cliente) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="services-container">
                                <div class="service-row mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="services" class="form-label">Servicio</label>
                                            <select class="form-control service-select" name="services[]" required>
                                                <option value="">Seleccione un servicio</option>
                                                @foreach ($services as $service)
                                                    <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">{{ $service->descripcion }} ({{ $service->precio }})</option>
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
                            </div>
                            <button type="button" class="btn btn-secondary mb-3" id="add-service-btn">Agregar Otro Servicio</button>
                            <br>
                            <button type="submit" class="btn btn-primary">Crear Cotización</button>
                            <a href="{{ route('lista') }}" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
                <a href="{{ url('/gestion_calidad') }}" class="back-btn">Volver al Dashboard</a>
            </div>
        </center>
    </div>

    @section('scripts')
        <!-- SweetAlert2 para mensajes de error -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('services-container');
                const addButton = document.getElementById('add-service-btn');

                addButton.addEventListener('click', function() {
                    const newRow = document.createElement('div');
                    newRow.classList.add('service-row', 'mb-3');
                    newRow.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <label for="services" class="form-label">Servicio</label>
                                <select class="form-control service-select" name="services[]" required>
                                    <option value="">Seleccione un servicio</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->services_id }}" data-precio="{{ $service->precio }}">{{ $service->descripcion }} ({{ $service->precio }})</option>
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
                    container.appendChild(newRow);
                    updateRemoveButtons();
                });

                container.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-service-btn')) {
                        e.target.closest('.service-row').remove();
                        updateRemoveButtons();
                    }
                });

                function updateRemoveButtons() {
                    const rows = container.querySelectorAll('.service-row');
                    rows.forEach((row, index) => {
                        const removeBtn = row.querySelector('.remove-service-btn');
                        removeBtn.style.display = index === 0 && rows.length === 1 ? 'none' : 'block';
                    });
                }
            });
        </script>
    @endsection
@endsection