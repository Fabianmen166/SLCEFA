@extends('layouts.master')

@section('contenido')
<style>
    .custom-header {
        background: linear-gradient(90deg, #007bff, #00bfff);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
        text-align: center;
        font-size: 1.8rem;
        font-weight: 600;
    }

    .quote-box {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: visible; /* Para permitir que el dropdown se salga */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        background-color: #ffffff;
    }

    .table-responsive {
        margin-top: 15px;
        overflow-x: auto;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border: 1px solid #dee2e6;
    }

    .table th, .table td {
        text-align: center;
        vertical-align: middle;
        padding: 12px;
        background-color: #fff;
    }

    .table th {
        background-color: #f1f1f1;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        background-color: #f9f9f9;
    }

    .form-search {
        display: flex;
        gap: 12px;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .form-search input {
        max-width: 400px;
    }

    .back-btn {
        margin-top: 30px;
        padding: 10px 30px;
        background-color: #6c757d;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #5a6268;
    }

    .alert {
        margin-top: 10px;
        font-size: 0.95rem;
    }

    .list-unstyled {
        padding-left: 0;
        margin-bottom: 0;
        text-align: left;
    }

    .list-unstyled li {
        padding: 2px 0;
    }

    /* Dropdown styles */
    .dropdown-container {
        position: relative;
        display: inline-block;
        z-index: 10; /* Valor base */
    }

    .dropdown-btn {
        background-color: #007bff;
        color: white;
        padding: 8px 14px;
        font-size: 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        white-space: nowrap;
    }

    .dropdown-btn:hover {
        background-color: #0056b3;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 180px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
        border-radius: 6px;
        z-index: 20; /* Valor base para el contenido */
        top: 100%; /* Asegura que el menú se muestre debajo del botón */
        left: 0; /* Alinea el menú con el botón */
        padding: 5px 0;
        white-space: nowrap;
    }

    .dropdown-container.active .dropdown-content {
        display: block;
    }

    .dropdown-content a,
    .dropdown-content .dropdown-link {
        color: black;
        padding: 10px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.2s;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .dropdown-content a:hover,
    .dropdown-content .dropdown-link:hover {
        background-color: #f1f1f1;
    }

    .dropdown-link.danger {
        color: #c82333;
    }
</style>

<div class="container py-4">
    <div class="text-center mb-4">
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="200px" alt="Logo Agrosoft">
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    @endif

    <div class="quote-box">
        <div class="custom-header">Lista de Cotizaciones</div>
        <div class="p-4">

            <form method="GET" action="{{ route('cotizacion.index') }}" class="form-search mb-4">
                <input type="text" class="form-control" name="nit" placeholder="Buscar por NIT o ID de cotización" value="{{ request('nit') }}">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </form>

            <div class="mb-3">
                <a href="{{ route('cotizacion.create') }}" class="btn btn-success">Crear Nueva Cotización</a>
            </div>

            @if ($quotes->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente (NIT)</th>
                                <th>Tipo de Solicitante</th>
                                <th>Servicios y Paquetes</th>
                                <th>Total</th>
                                <th>Creado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td>{{ $quote->quote_id ?? 'N/A' }}</td>
                                    <td>{{ $quote->customer->nit ?? 'N/A' }}</td>
                                    <td>{{ $quote->customer->solicitante ?? 'N/A' }}</td>
                                    <td>
                                        @if ($quote->quoteServices->isNotEmpty())
                                            <ul class="list-unstyled">
                                                @foreach ($quote->quoteServices as $quoteService)
                                                    @if ($quoteService->services_id && $quoteService->service)
                                                        <li><strong>{{ $quoteService->service->descripcion ?? 'Servicio no encontrado' }}</strong> (x{{ $quoteService->cantidad }})</li>
                                                    @elseif ($quoteService->service_packages_id && $quoteService->servicePackage)
                                                        <li><strong>{{ $quoteService->servicePackage->nombre ?? 'Paquete no encontrado' }}</strong> (x{{ $quoteService->cantidad }})</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">Sin servicios</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($quote->total, 2) }}</td>
                                    <td>{{ $quote->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="dropdown-container">
                                            <button class="dropdown-btn">Acciones</button>
                                            <div class="dropdown-content">
                                                <a href="{{ route('cotizacion.show', $quote->quote_id) }}">Ver</a>
                                                <a href="{{ route('cotizacion.edit', $quote->quote_id) }}">Editar</a>
                                                <form action="{{ route('cotizacion.destroy', $quote->quote_id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta cotización?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-link danger">Eliminar</button>
                                                </form>
                                                <a href="{{ route('cotizacion.comprobante', $quote->quote_id) }}">PDF</a>
                                                <a href="{{ route('cotizacion.show_upload_form', $quote->quote_id) }}">Subir Comprobante</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center">No se encontraron cotizaciones.</div>
            @endif

            <div class="text-center">
                <a href="{{ route('gestion_calidad.dashboard') }}" class="back-btn">← Volver al Dashboard</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdowns = document.querySelectorAll('.dropdown-container');

    dropdowns.forEach((dropdown, index) => {
        const btn = dropdown.querySelector('.dropdown-btn');
        const content = dropdown.querySelector('.dropdown-content');

        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            // Cerrar otros menús y restaurar su z-index
            dropdowns.forEach((otherDropdown, otherIndex) => {
                if (otherIndex !== index) {
                    otherDropdown.classList.remove('active');
                    otherDropdown.style.zIndex = '10'; // Restaurar z-index base
                }
            });

            // Alternar el menú actual y ajustar z-index
            const isActive = dropdown.classList.contains('active');
            dropdown.classList.toggle('active', !isActive);

            // Ajustar z-index para que el menú activo esté por encima
            dropdown.style.zIndex = isActive ? '10' : '1000';
        });
    });

    // Cerrar los menús al hacer clic fuera
    document.addEventListener('click', () => {
        dropdowns.forEach((dropdown) => {
            dropdown.classList.remove('active');
            dropdown.style.zIndex = '10'; // Restaurar z-index base
        });
    });

    // Evitar que el clic en el contenido del menú lo cierre
    document.querySelectorAll('.dropdown-content').forEach((content) => {
        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
});
</script>
@endsection