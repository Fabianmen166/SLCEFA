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
                    <h5 class="card-header">Lista de Cotizaciones</h5>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="GET" action="{{ route('lista') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="nit" class="form-label">Buscar por NIT del Cliente</label>
                                    <input type="text" class="form-control" id="nit" name="nit" value="{{ request('nit') }}" placeholder="NIT del Cliente">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrar</button>
                                    <a href="{{ route('lista') }}" class="btn btn-secondary ms-2">Limpiar</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Cotización</th>
                                        <th>NIT Cliente</th>
                                        <th>Tipo Cliente</th>
                                        <th>Total</th>
                                        <th>Servicios</th>
                                        <th>Archivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($quotes as $quote)
                                        <tr>
                                            <td>{{ $quote->quote_id }}</td>
                                            <td>{{ $quote->customer->nit }}</td>
                                            <td>{{ ucfirst($quote->customer->tipo_cliente) }}</td>
                                            <td>{{ number_format($quote->total, 2) }}</td>
                                            <td>
                                                @foreach ($quote->services as $service)
                                                    {{ $service->descripcion }} (Cant: {{ $service->pivot->cantidad }}, Subtotal: {{ number_format($service->pivot->subtotal, 2) }})<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if ($quote->archivo)
                                                    <a href="{{ asset('storage/comprobantes/' . $quote->archivo) }}" target="_blank">Ver Archivo</a>
                                                @else
                                                    No subido
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('cotizacion.edit', $quote->quote_id) }}" class="btn btn-success btn-sm">Editar</a>
                                                <button type="button" class="btn btn-danger btn-sm deletebtn"
                                                        data-id="{{ $quote->quote_id }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#eliminar">Eliminar</button>
                                                <a href="{{ route('quote.upload', $quote->quote_id) }}" class="btn btn-warning btn-sm">Subir Archivo</a>
                                                <a href="{{ route('quote.pdf', $quote->quote_id) }}" class="btn btn-info btn-sm">Generar PDF</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">No hay cotizaciones registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="eliminar" tabindex="-1" aria-labelledby="eliminarLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form id="formEliminar" method="POST" action="">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarLabel">Eliminar Cotización</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="delete-id">
                                            <p>¿Estás seguro de que deseas eliminar esta cotización?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ url('/gestion_calidad') }}" class="back-btn">Volver al Dashboard</a>
            </div>
        </center>
    </div>

    @section('scripts')
        <!-- Bootstrap JS and Popper.js -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
        <!-- SweetAlert2 para mensajes de error -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Delete
                document.querySelectorAll('.deletebtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const form = document.getElementById('formEliminar');
                        form.action = "{{ url('cotizacion') }}/" + id; // Asegurar que la URL sea correcta
                        document.getElementById('delete-id').value = id;
                    });
                });
            });
        </script>
    @endsection
@endsection