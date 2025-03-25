<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $quote->quote_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px; /* Reducir márgenes para aprovechar espacio */
            color: #333;
            text-align: center; /* Centrar todo el contenido */
        }
        .header {
            margin-bottom: 10px; /* Reducir espacio */
            background-color: #e6f4ea; /* Verde suave */
            padding: 8px; /* Reducir padding */
            border-radius: 5px;
        }
        .header img {
            max-width: 150px; /* Reducir tamaño del logo */
        }
        .header-title {
            font-size: 20px; /* Reducir tamaño de fuente */
            font-weight: bold;
            color: #2e7d32; /* Verde oscuro */
            margin: 5px 0; /* Reducir margen */
        }
        .divider {
            border-top: 2px solid #a5d6a7; /* Línea verde clara */
            margin: 10px 0; /* Reducir margen */
        }
        .title {
            font-size: 16px; /* Reducir tamaño de fuente */
            font-weight: bold;
            color: #2e7d32; /* Verde oscuro */
            margin: 8px 0; /* Reducir margen */
        }
        .info, .filler, .extra-info {
            margin-bottom: 10px; /* Reducir espacio */
        }
        .info p, .filler p, .extra-info p {
            margin: 3px 0; /* Reducir margen */
            font-size: 12px; /* Reducir tamaño de fuente */
        }
        table.services-table, table.signature-table {
            width: 80%; /* Reducir ancho de las tablas */
            margin: 0 auto 10px auto; /* Centrar y reducir margen */
            border-collapse: collapse;
        }
        table.services-table, table.services-table th, table.services-table td,
        table.signature-table, table.signature-table th, table.signature-table td {
            border: 1px solid #a5d6a7; /* Verde claro */
        }
        table.services-table th, table.signature-table th {
            background-color: #c8e6c9; /* Verde muy suave */
            color: #2e7d32; /* Verde oscuro */
            padding: 5px; /* Reducir padding */
            text-align: center; /* Centrar texto */
            font-size: 12px; /* Reducir tamaño de fuente */
        }
        table.services-table td, table.signature-table td {
            padding: 5px; /* Reducir padding */
            text-align: center; /* Centrar texto */
            font-size: 12px; /* Reducir tamaño de fuente */
        }
        .total {
            font-weight: bold;
            font-size: 14px; /* Reducir tamaño de fuente */
            color: #2e7d32; /* Verde oscuro */
            margin: 5px 0; /* Reducir margen */
        }
        .services-text {
            margin-bottom: 10px; /* Reducir espacio */
            font-size: 12px; /* Reducir tamaño de fuente */
            line-height: 1.3; /* Reducir interlineado */
        }
        .signature-section {
            margin-top: 10px; /* Reducir espacio */
        }
    </style>
</head>
<body>
    <!-- Encabezado dinámico según certificación -->
    <div class="header">
        <img src="{{ public_path($isAccredited ? 'images/LogoAgrosoft2_certificado.png' : 'images/LogoAgrosoft2.png') }}" alt="Logo">
        <div class="header-title">
            {{ $isAccredited ? 'Cotización Certificada' : 'Cotización No Certificada' }}
        </div>
    </div>

    <!-- Línea divisoria con título -->
    <div class="divider"></div>
    <div class="title">Cotización #{{ $quote->quote_id }}</div>
    <div class="divider"></div>

    <!-- Datos del solicitante y fecha -->
    <div class="info">
        <div class="title">Datos del Solicitante</div>
        <p><strong>Solicitante:</strong> {{ $quote->customer->solicitante }}</p>
        <p><strong>Contacto:</strong> {{ $quote->customer->contacto }}</p>
        <p><strong>Teléfono:</strong> {{ $quote->customer->telefono }}</p>
        <p><strong>NIT:</strong> {{ $quote->customer->nit }}</p>
        <p><strong>Correo:</strong> {{ $quote->customer->correo ?? 'No proporcionado' }}</p>
        <p><strong>Tipo de Cliente:</strong> {{ ucfirst($quote->customer->tipo_cliente) }}</p>
        <p><strong>Fecha de la Cotización:</strong> {{ $quote->created_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Línea divisoria con título -->
    <div class="divider"></div>
    <div class="title">Información General</div>
    <div class="divider"></div>

    <!-- Información general de la empresa y ID de la cotización -->
    <div class="info">
        <p><strong>Nombre:</strong> Agrosoft S.A.S.</p>
        <p><strong>Dirección:</strong> Calle 123 #45-67, Ciudad, País</p>
        <p><strong>Teléfono:</strong> +57 123 456 7890</p>
        <p><strong>Correo:</strong> contacto@agrosoft.com</p>
        <p><strong>ID de la Cotización:</strong> {{ $quote->quote_id }}</p>
    </div>

    <!-- Tabla de servicios -->
    <div class="title">Servicios</div>
    <table class="services-table">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->services as $service)
                <tr>
                    <td>{{ $service->descripcion }}</td>
                    <td>{{ $service->pivot->cantidad }}</td>
                    <td>{{ number_format($service->pivot->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">Total: {{ number_format($quote->total, 2) }}</p>

    <!-- Información según el cliente -->
    <div class="info">
        <div class="title">Información del Cliente</div>
        <p>{{ $clientText }}</p>
    </div>

    <!-- Información de relleno -->
    <div class="filler">
        <div class="title">Información de Relleno</div>
        <p>Este es un texto de relleno para cumplir con los requisitos del documento. Aquí puedes incluir información adicional sobre la cotización o términos y condiciones.</p>
    </div>

    <!-- Servicios en texto (párrafo) -->
    <div class="services-text">
        <div class="title">Descripción de los Servicios</div>
        <p>
            Los servicios incluidos en esta cotización son: 
            @foreach ($quote->services as $index => $service)
                {{ $service->descripcion }} (Cantidad: {{ $service->pivot->cantidad }}, Subtotal: {{ number_format($service->pivot->subtotal, 2) }})
                @if ($index < $quote->services->count() - 1), @endif
            @endforeach.
        </p>
    </div>

    <!-- Información extra -->
    <div class="extra-info">
        <div class="title">Información Extra</div>
        <p>Esta sección contiene información adicional relevante para el cliente, como garantías o soporte técnico.</p>
    </div>

    <!-- Más información de relleno -->
    <div class="filler">
        <div class="title">Más Información de Relleno</div>
        <p>Este es otro bloque de texto de relleno. Personalízalo según las necesidades de tu empresa o cliente.</p>
    </div>

    <!-- Elaborado y aprobado -->
    <div class="info">
        <div class="title">Responsables</div>
        <p><strong>Elaborado por:</strong> {{ $quote->user->name }} - Cargo: Analista de Cotizaciones</p>
        <p><strong>Aprobado por:</strong> Juan Pérez - Gerente General</p>
    </div>

    <!-- Tabla para nombre, CC, fecha y firma -->
    <div class="signature-section">
        <div class="title">Firmas</div>
        <table class="signature-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>C.C.</th>
                    <th>Fecha</th>
                    <th>Firma</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 30px;"></td> <!-- Reducir altura de la fila -->
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>