<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $quote->quote_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            color: #333;
            text-align: center;
        }
        .header {
            margin-bottom: 10px;
            background-color: #e6f4ea;
            padding: 8px;
            border-radius: 5px;
        }
        .header img {
            max-width: 150px;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            color: #2e7d32;
            margin: 5px 0;
        }
        .divider {
            border-top: 2px solid #a5d6a7;
            margin: 10px 0;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #2e7d32;
            margin: 8px 0;
        }
        .info, .filler, .extra-info {
            margin-bottom: 10px;
        }
        .info p, .filler p, .extra-info p {
            margin: 3px 0;
            font-size: 12px;
        }
        table.services-table, table.signature-table {
            width: 80%;
            margin: 0 auto 10px auto;
            border-collapse: collapse;
        }
        table.services-table, table.services-table th, table.services-table td,
        table.signature-table, table.signature-table th, table.signature-table td {
            border: 1px solid #a5d6a7;
        }
        table.services-table th, table.signature-table th {
            background-color: #c8e6c9;
            color: #2e7d32;
            padding: 5px;
            text-align: center;
            font-size: 12px;
        }
        table.services-table td, table.signature-table td {
            padding: 5px;
            text-align: center;
            font-size: 12px;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
            color: #2e7d32;
            margin: 5px 0;
        }
        .services-text {
            margin-bottom: 10px;
            font-size: 12px;
            line-height: 1.3;
        }
        .signature-section {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Encabezado dinámico según certificación -->
    <div class="header">
        <img src="{{ public_path('images/LogoAgrosoft2.png') }}" alt="Logo">
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
        <p><strong>Solicitante:</strong> {{ $quote->customer->razon_social ?? 'No disponible' }}</p>
        <p><strong>Contacto:</strong> {{ $quote->customer->contacto ?? 'No disponible' }}</p>
        <p><strong>Teléfono:</strong> {{ $quote->customer->telefono ?? 'No disponible' }}</p>
        <p><strong>NIT:</strong> {{ $quote->customer->nit ?? 'No disponible' }}</p>
        <p><strong>Correo:</strong> {{ $quote->customer->correo ?? 'No Reportado' }}</p>
        <p><strong>Tipo de Cliente:</strong> {{ ucfirst($quote->customer->customerType->name ?? $quote->customer->tipo_cliente ?? 'Desconocido') }}</p>
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
            @foreach ($quote->quoteServices as $quoteService)
                <tr>
                    <td>
                        @if ($quoteService->service)
                            {{ $quoteService->service->descripcion }}
                        @elseif ($quoteService->servicePackage)
                            {{ $quoteService->servicePackage->nombre }}
                        @endif
                    </td>
                    <td>{{ $quoteService->cantidad }}</td>
                    <td>{{ number_format($quoteService->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="total">Total: {{ number_format($quote->total, 2) }}</p>

    <!-- Información según el cliente -->
    <div class="info">
        <div class="title">Información del Cliente</div>
        <p>{{ $clientText ?? 'Tipo de cliente no especificado.' }}</p>
    </div>

    <!-- Información de relleno -->
    <div class="filler">
        <div class="title">Información de Relleno</div>
        <p>Esta cotización tiene una vigencia de 30 días calendario. La aceptación de la oferta implica que el cliente está de acuerdo con todas las condiciones aquí descritas, incluyendo que sus muestras se analicen por los métodos indicados. En caso de tener cualquier inconformidad la debe manifestar al laboratorio para elaborar una nueva cotización.</p>
        <p>La cantidad de muestra requerida es de aproximadamente 1 kg, la cual debe be empacada en bolsa limpia, seca, bien sellada y rotulada. La recepción de las muestras para análisis fisicoquímico se hará de lunes a viernes de 08:00 h a 15:00 h en. La entrega de resultados se hará en aproximadamente (15 días hábiles contados a partir del día siguiente de la recepción de la muestra) y será acordada previamente con el cliente; una vez emitidos, dicha entrega se realizará en las instalaciones del laboratorio en los horarios de lunes a viernes de 08:00 h a 12:00 h y de 13:30 h a 16:00 h o vía correo electrónico si así lo desea el cliente. En caso de que el cliente requiera devolución del ítem de ensayo, deberá acercarse al laboratorio a partir de la fecha de elaboración del informe de resultados sin exceder un 30 días, de lo contrario el laboratorio queda autorizado para hacer la disposición final.</p>
    </div>

    <!-- Servicios en texto (párrafo) -->
    <div class="services-text">
        <div class="title">Descripción de los Servicios</div>
        <p>Los servicios incluidos en esta cotización son:
            @foreach ($quote->quoteServices as $quoteService)
                @if ($quoteService->service)
                    {{ $quoteService->service->descripcion }} (Cantidad: {{ $quoteService->cantidad }}, Subtotal: {{ number_format($quoteService->subtotal, 2) }})
                @elseif ($quoteService->servicePackage)
                    {{ $quoteService->servicePackage->nombre }} (Cantidad: {{ $quoteService->cantidad }}, Subtotal: {{ number_format($quoteService->subtotal, 2) }}), que incluye: 
                    @php
                        $services = $quoteService->servicePackage->included_services;
                        $serviceDescriptions = [];
                        if (is_string($services)) {
                            $serviceIds = json_decode($services, true) ?? [];
                            foreach ($serviceIds as $serviceId) {
                                $service = \App\Models\Service::find($serviceId);
                                if ($service) {
                                    $serviceDescriptions[] = $service->descripcion;
                                }
                            }
                        }
                    @endphp
                    {{ implode(', ', $serviceDescriptions) }}
                @endif
                @if (!$loop->last), @endif
            @endforeach.
        </p>
    </div>

    <!-- Información extra -->
    <div class="extra-info">
        <div class="title">Información Extra</div>
        <p>Condiciones para divulgación de la información del cliente:</p>
        <p>Toda la información recibida del cliente por cualquiera de los canales disponibles (presencial, telefónico, correo electrónico, etc) o la generada en el laboratorio a partir de su solicitud, se considera confidencial. Sin embargo, pretende poner información del cliente al alcance del público, podrá hacerlo si se cumplen las siguientes condiciones:</p>
        <p>1. Se debe contar con el consentimiento del cliente para que su información pueda ser compartida, o que el mismo, luego de recibirla la publique a su conveniencia. Cuando el laboratorio necesite utilizar información del cliente y ponerla al alcance del público se debe tener con antelación su aprobación por medio de un correo electrónico con la aceptación respectiva.</p>
        <p>2. Si por disposición de un juez de la república, el LCB tiene que hacer pública la información correspondiente a los resultados de los ensayos de un cliente. Se deberá hacer dicha publicación y posteriormente notificar al cliente de la situación, a menos que en el mismo requerimiento legal, no sea posible informarle.</p>
        <p>Nota: Una vez se llegue a un acuerdo entre las partes y se realice la prestación del servicio, la información sobre el cliente obtenida de fuentes distintas del cliente (por ejemplo, la que proviene de un denunciante o los organismos reglamentarios) se mantendrá confidencial entre el cliente y el laboratorio. El proveedor (fuente) de esta información es confidencial para el laboratorio y no se comparte con el cliente, a menos que la fuente lo autorice.</p>
    </div>

    <!-- Más información de relleno -->
    <div class="filler">
        <div class="title">Más Información de Relleno</div>
        <p>Otras consideraciones:</p>
        <p>1. El laboratorio de Ciencias Básicas sección fisicoquímica, no proporciona información sobre declaraciones de conformidad respecto a una especificación, norma o partes de ésta (requisito 7.8.6 NTC ISO/IEC 17025:2017) y no emite información sobre opiniones e interpretaciones (requisito 7.8.7 NTC ISO/IEC 17025:2017).</p>
        <p>2. No realiza muestreo, por lo tanto es responsabilidad del cliente realizar o subcontratar esta actividad y suministrar toda la información necesaria de la muestra (Fecha y hora de muestreo, metodología empleada para la recolección de la muestra). Si el cliente no suministra dicha información u otra que pueda influir en la validez de los resultados, la muestra se recibirá bajo su responsabilidad y se dejará constancia del caso.</p>
        <p>3. En SENA-SERVICIO NACIONAL DE APRENDIZAJE con sede en el laboratorio de ciencias básicas en el centro de formación AGROINDUSTRIAL regional Huila, contamos con acreditación ONAC, vigente a la fecha, con código de acreditación.</p>
        <p>4. En ninguna circunstancia el cliente está autorizado para el uso del símbolo de.</p>
    </div>

    <!-- Elaborado y aprobado -->
    <div class="info">
        <div class="title">Responsables</div>
        <p><strong>Elaborado por:</strong> {{ $quote->user->name }} - Cargo: Analista de Cotizaciones</p>
        <p><strong>Aprobado por:</strong> {{ $admin->name }} - Gerente General</p>
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
                    <td style="height: 30px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>