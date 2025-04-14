<!DOCTYPE html>
<html>
<head>
    <title>Editar Cliente</title>
</head>
<body>
    <h1>Editar Cliente</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div style="color: red;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('customers.update', $customer->customers_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="solicitante">Solicitante:</label>
            <input type="text" name="solicitante" id="solicitante" value="{{ old('solicitante', $customer->solicitante) }}">
        </div>
        <div>
            <label for="contacto">Contacto:</label>
            <input type="text" name="contacto" id="contacto" value="{{ old('contacto', $customer->contacto) }}" required>
        </div>
        <div>
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $customer->telefono) }}" required>
        </div>
        <div>
            <label for="nit">NIT:</label>
            <input type="text" name="nit" id="nit" value="{{ old('nit', $customer->nit) }}" required>
        </div>
        <div>
            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" value="{{ old('correo', $customer->correo) }}">
        </div>
        <div>
            <label for="customer_type_id">Tipo de Cliente:</label>
            <select name="customer_type_id" id="customer_type_id" required>
                <option value="">Seleccione un tipo de cliente</option>
                @foreach($customerTypes as $customerType)
                    <option value="{{ $customerType->customer_type_id }}" {{ old('customer_type_id', $customer->customer_type_id) == $customerType->customer_type_id ? 'selected' : '' }}>
                        {{ $customerType->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit">Actualizar</button>
    </form>

    <a href="{{ route('listac') }}">Volver</a>
</body>
</html>