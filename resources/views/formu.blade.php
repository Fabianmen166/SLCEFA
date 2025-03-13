<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cotización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Crear Cotización</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('cotizacion.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nombre_empresa" class="form-label">Nombre Empresa (Opcional)</label>
                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="{{ old('nombre_empresa') }}">
            </div>
            <div class="mb-3">
                <label for="nombre_persona" class="form-label">Nombre Persona</label>
                <input type="text" class="form-control" id="nombre_persona" name="nombre_persona" value="{{ old('nombre_persona') }}" required>
            </div>
            <div class="mb-3">
                <label for="nit" class="form-label">NIT</label>
                <input type="text" class="form-control" id="nit" name="nit" value="{{ old('nit') }}" required>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="{{ old('direccion') }}" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo (Opcional)</label>
                <input type="email" class="form-control" id="correo" name="correo" value="{{ old('correo') }}">
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ old('precio') }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="{{ route('lista') }}" class="btn btn-secondary">Volver</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>