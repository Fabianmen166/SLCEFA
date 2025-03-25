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
                    <h5 class="card-header">Editar Cliente #{{ $customer->customers_id }}</h5>
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

                        <form method="POST" action="{{ route('customers.update', $customer->customers_id) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="solicitante" class="form-label">Solicitante</label>
                                <input type="text" class="form-control" id="solicitante" name="solicitante" value="{{ old('solicitante', $customer->solicitante) }}">
                            </div>
                            <div class="mb-3">
                                <label for="contacto" class="form-label">Contacto</label>
                                <input type="text" class="form-control" id="contacto" name="contacto" value="{{ old('contacto', $customer->contacto) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono', $customer->telefono) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="nit" class="form-label">NIT</label>
                                <input type="text" class="form-control" id="nit" name="nit" value="{{ old('nit', $customer->nit) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="correo" name="correo" value="{{ old('correo', $customer->correo) }}">
                            </div>
                            <div class="mb-3">
                                <label for="tipo_cliente" class="form-label">Tipo de Cliente</label>
                                <select class="form-control" id="tipo_cliente" name="tipo_cliente" required>
                                    <option value="externo" {{ old('tipo_cliente', $customer->tipo_cliente) == 'externo' ? 'selected' : '' }}>Externo</option>
                                    <option value="interno" {{ old('tipo_cliente', $customer->tipo_cliente) == 'interno' ? 'selected' : '' }}>Interno</option>
                                    <option value="trabajador" {{ old('tipo_cliente', $customer->tipo_cliente) == 'trabajador' ? 'selected' : '' }}>Trabajador</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                            <a href="{{ route('listac') }}" class="btn btn-secondary">Cancelar</a>
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
    @endsection
@endsection