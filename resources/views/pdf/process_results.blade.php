<!-- resources/views/pdf/process_results.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Resultados del Proceso {{ $process->process_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 16px; }
        h2 { font-size: 14px; }
        p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Resultados del Proceso {{ $process->process_id }}</h1>
    <p><strong>Cotización:</strong> {{ $process->quote->quote_id ?? 'N/A' }}</p>
    <p><strong>Cliente:</strong> {{ $process->quote->customer->contacto ?? 'Sin cliente' }}</p>
    <h2>Servicios Realizados</h2>
    <table>
        <thead>
            <tr>
                <th>Servicio/Paquete</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($process->quote->quoteServices as $quoteService)
                <tr>
                    <td>
                        @if($quoteService->service)
                            {{ $quoteService->service->descripcion }}
                        @elseif($quoteService->servicePackage)
                            {{ $quoteService->servicePackage->nombre }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $quoteService->cantidad ?? 'N/A' }}</td>
                    <td>{{ number_format($quoteService->subtotal ?? 0, 2) }}</td>
                    <td>
                        @php
                            $analysis = $process->analyses->firstWhere('service_id', $quoteService->service ? $quoteService->service->services_id : ($quoteService->servicePackage ? $quoteService->servicePackage->service_packages_id : null));
                        @endphp
                        {{ $analysis ? ucfirst($analysis->status) : 'N/A' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No se encontraron servicios.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>