<!DOCTYPE html>
<html>
<head>
    <title>Cotización {{ $quote->quote_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Cotización #{{ $quote->quote_id }}</h1>
    <p><strong>Cliente:</strong> {{ $quote->customer->solicitante }} (NIT: {{ $quote->customer->nit }})</p>
    <p><strong>Usuario:</strong> {{ $quote->user->name }}</p>
    <p><strong>Fecha:</strong> {{ $quote->created_at->format('d/m/Y') }}</p>

    <h3>Servicios</h3>
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->services as $service)
                <tr>
                    <td>{{ $service->descripcion }}</td>
                    <td>{{ $service->pivot->cantidad }}</td>
                    <td>{{ number_format($service->precio, 2) }}</td>
                    <td>{{ number_format($service->pivot->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Paquetes de Servicios</h3>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->servicePackages as $package)
                <tr>
                    <td>{{ $package->nombre }}</td>
                    <td>{{ $package->pivot->cantidad }}</td>
                    <td>{{ number_format($package->precio, 2) }}</td>
                    <td>{{ number_format($package->pivot->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($quote->total, 2) }}</h3>
</body>
</html>