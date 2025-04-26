<!-- resources/views/pdf/process_results.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Resultados del Proceso {{ $process->process_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Resultados del Proceso {{ $process->process_id }}</h1>
    <p><strong>Cotización:</strong> {{ $process->quote->quote_id }}</p>
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
            @foreach($process->quote->quoteServices as $quoteService)
                <tr>
                    <td>
                        @if($quoteService->service)
                            {{ $quoteService->service->descripcion }}
                        @elseif($quoteService->servicePackage)
                            {{ $quoteService->servicePackage->nombre }}
                        @endif
                    </td>
                    <td>{{ $quoteService->cantidad }}</td>
                    <td>{{ number_format($quoteService->subtotal, 2) }}</td>
                    <td>
                        @php
                            $analysis = $process->analyses->firstWhere('service_id', $quoteService->service ? $quoteService->service->services_id : $quoteService->servicePackage->service_packages_id);
                        @endphp
                        {{ $analysis ? ucfirst($analysis->status) : 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>