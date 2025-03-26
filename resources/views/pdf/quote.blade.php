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
        <img src="{{ public_path($isAccredited ? 'img/logo_acreditado.jpg' : 'img/logo.jpg') }}" alt="Logo">
        <div class="header-title">
            {{ $isAccredited ? 'Cotización Certificada F-LCB-001' : 'Cotización No Certificada F-LCB-033 ' }}
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
        <p><strong>Correo:</strong> {{ $quote->customer->correo ?? 'No Reportado' }}</p>
        <p><strong>Tipo de Cliente:</strong> {{ ucfirst($quote->customer->tipo_cliente) }}</p>
        <p><strong>Fecha de la Cotización:</strong> {{ $quote->created_at->format('Y/m/d') }}</p>
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
        
        <p>{{ $clientText }}</p>
    </div>

    <!-- Información de relleno -->
    <div class="filler">
       
        <p>Esta cotización tiene una vigencia de 30 días calendario. La aceptación de la oferta implica que el cliente está de acuerdo con todas las condiciones aquí descritas, incluyendo que sus muestras se analicen por los métodos indicados. En caso de tener cualquier inconformidad la debe manifestar al laboratorio para elaborar una nueva cotización.</p>
    </div>

    <div class="filler">
       
        <p>La cantidad de muestra requerida es de aproximadamente 1 kg, la cual debe ser empacada en bolsa limpia, seca, bien sellada y rotulada. La recepción de las muestras para análisis fisicoquímico se hará de lunes a viernes de 08:00 h a 15:00 h en el Laboratorio de Ciencias Básicas del Centro de Formación Agroindustrial "La Angostura". La entrega de resultados se hará en aproximadamente (15 días hábiles contados a partir del día siguiente de la recepción de la muestra) y será acordada previamente con el cliente; una vez emitidos, dicha entrega se realizará en las instalaciones del laboratorio en los horarios de lunes a viernes de 08:00 h a 12:00 h y de 13:30 h a 16:00 h o vía correo electrónico si así lo desea el cliente. En caso de quel el cliente requiera devolución del ítem de ensayo, deberá acercarse al laboratorio a partir de la fecha de elaboración del informe de resultados sin exceder un 30 dias, de lo contrario el laboratorio queda autorizado para hacer la disposición final. </p>
    </div>

    <!-- Servicios en texto (párrafo) -->
    <div class="services-text">
        
        <p>
        El análisis Fisicoquímico de suelos completo incluye: 
            @foreach ($quote->services as $index => $service)
                {{ $service->descripcion }} 
                @if ($index < $quote->services->count() - 1), @endif
            @endforeach.

        </p>
        <p>Las actividades de evaluación de la conformidad marcadas con * no están incluidas en el certificado de acreditación.</p>
    </div>

    <!-- Información extra -->
    <div class="extra-info">
        <p>Condiciones para divulgación de la información del cliente:</p>
        <p>Toda la información recibida del cliente por cualquiera de los canales disponibles (presencial, telefónico, correo electrónico, etc) o la generada en el laboratorio a partir de su solicitud, se considera confidencial. Sin embargo, cuando el Laboratorio de Ciencias Básicas sección fisicoquímica, pretende poner información del cliente al alcance del público, podrá hacerlo si se cumplen las siguientes condiciones: </p>  <p>
1.	Se debe contar con el consentimiento del cliente para que su información pueda ser compartida, o que el mismo, luego de recibirla la publique a su conveniencia. Cuando el laboratorio necesite utilizar información del cliente y ponerla al alcance del público se debe tener con antelación su aprobación por medio de un correo electrónico con la aceptación respectiva.</p>  <p>
2.	Si por disposición de un juez de la república, el LCB tiene que hacer pública la información correspondiente a los resultados de los ensayos de un cliente. Se deberá hacer dicha publicación y posteriormente notificar al cliente de la situación, a menos que en el mismo requerimiento legal, no sea posible informarle.</p>  <p>
Nota: Una vez se llegue a un acuerdo entre las partes y se realice la prestación del servicio, la información sobre el cliente obtenida de fuentes distintas del cliente (por ejemplo, la que proviene de un  denunciante o los organismos reglamentarios) se mantendrá confidencial entre el cliente y el laboratorio. El proveedor (fuente) de esta información es confidencial para el laboratorio y no se comparte con el cliente, a menos que la fuente lo autorice.</p>
    </div>

    <!-- Más información de relleno -->
    <div class="filler">
        <p>Otras consideraciones:</p>
        <p>1. El laboratorio de Ciencias Básicas sección fisicoquímica, no proporciona infomación sobre declaraciones de conformidad respecto a una especificación, norma o partes de ésta (requisito 7.8.6 NTC ISO/IEC 17025:2017) y no emite información sobre opiniones e interpretaciones (requisito 7.8.7 NTC ISO/IEC 17025:2017).  </p> <p>
2. El laboratorio de Ciencias Básicas sección fisicoquímica, no realiza muestreo, por lo tanto es responsabilidad del cliente realizar o subcontratar esta actividad y suministrar toda la información necesaria de la muestra (Fecha y hora de muestreo, metodología empleada para la recolección de la muestra). Si el cliente no suministra dicha información u otra que pueda influir en la validez de los resultados, la muestra se recibirá bajo su responsabilidad y se dejará constancia del caso.  </p> <p>                                                                                                                                                                                                                                        
3. En SENA-SERVICIO NACIONAL DE APRENDIZAJE con sede en el laboratorio de ciencias basicas en el centro de formación AGROINDUSTRIAL regional Huila, contamos con acreditación ONAC, vigente a la fecha, con código de acreditación 22-LAB-045, bajo la norma NTC ISO/IEC 17025:2017.    </p> <p>                                                                                                                                    
4. En ninguna circunstancia el cliente está autorizado para el uso del símbolo de acreditado de ONAC.      </p>
    </div>

    <!-- Elaborado y aprobado -->
    <div class="info">
        <div class="title">Responsables</div>
        <p><strong>Elaborado por:</strong> {{ $quote->user->name }} - Cargo: Analista de Cotizaciones</p>
        <p><strong>Aprobado por:</strong> Elcy ramirez - encargado laboratorio</p>
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