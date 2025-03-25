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
                    <h5 class="card-header">Lista de Clientes</h5>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="GET" action="{{ route('listac') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="nombre" class="form-label">Buscar por Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ request('nombre') }}" placeholder="Nombre de persona o empresa">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrar</button>
                                    <a href="{{ route('listac') }}" class="btn btn-secondary ms-2">Limpiar</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Solicitante</th>
                                        <th>Contacto</th>
                                        <th>Teléfono</th>
                                        <th>NIT</th>
                                        <th>Correo</th>
                                        <th>Tipo Cliente</th>
                                        <th>Editar</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($customers as $item)
                                        <tr>
                                            <td>{{ $item->solicitante }}</td>
                                            <td>{{ $item->contacto }}</td>
                                            <td>{{ $item->telefono }}</td>
                                            <td>{{ $item->nit }}</td>
                                            <td>{{ $item->correo }}</td>
                                            <td>{{ ucfirst($item->tipo_cliente) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-success editbtn"
                                                        data-id="{{ $item->customers_id }}"
                                                        data-solicitante="{{ $item->solicitante }}"
                                                        data-contacto="{{ $item->contacto }}"
                                                        data-telefono="{{ $item->telefono }}"
                                                        data-nit="{{ $item->nit }}"
                                                        data-correo="{{ $item->correo }}"
                                                        data-tipo_cliente="{{ $item->tipo_cliente }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editar">Editar</button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger deletebtn"
                                                        data-id="{{ $item->customers_id }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#eliminar">Eliminar</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">No hay clientes registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editar" tabindex="-1" aria-labelledby="editarLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form id="formEditar" method="POST" action="">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarLabel">Editar Cliente</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="edit-solicitante" class="form-label">Solicitante</label>
                                                <input type="text" class="form-control" id="edit-solicitante" name="solicitante">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-contacto" class="form-label">Contacto</label>
                                                <input type="text" class="form-control" id="edit-contacto" name="contacto" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="edit-telefono" name="telefono" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-nit" class="form-label">NIT</label>
                                                <input type="text" class="form-control" id="edit-nit" name="nit" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-correo" class="form-label">Correo</label>
                                                <input type="email" class="form-control" id="edit-correo" name="correo">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-tipo_cliente" class="form-label">Tipo de Cliente</label>
                                                <select class="form-control" id="edit-tipo_cliente" name="tipo_cliente" required>
                                                    <option value="externo">Externo</option>
                                                    <option value="interno">Interno</option>
                                                    <option value="trabajador">Trabajador</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="eliminar" tabindex="-1" aria-labelledby="eliminarLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form id="formEliminar" method="POST" action="">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="eliminarLabel">Eliminar Cliente</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="delete-id">
                                            <p>¿Estás seguro de que deseas eliminar este cliente?</p>
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
        <!-- SweetAlert2 for error messages -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Edit
                document.querySelectorAll('.editbtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const form = document.getElementById('formEditar');
                        form.action = "{{ url('customers') }}/" + id; // Asegurar que la URL sea correcta
                        document.getElementById('edit-solicitante').value = this.getAttribute('data-solicitante') || '';
                        document.getElementById('edit-contacto').value = this.getAttribute('data-contacto') || '';
                        document.getElementById('edit-telefono').value = this.getAttribute('data-telefono') || '';
                        document.getElementById('edit-nit').value = this.getAttribute('data-nit') || '';
                        document.getElementById('edit-correo').value = this.getAttribute('data-correo') || '';
                        document.getElementById('edit-tipo_cliente').value = this.getAttribute('data-tipo_cliente') || 'externo';
                    });
                });

                // Delete
                document.querySelectorAll('.deletebtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const form = document.getElementById('formEliminar');
                        form.action = "{{ url('customers') }}/" + id; // Asegurar que la URL sea correcta
                        document.getElementById('delete-id').value = id;
                    });
                });
            });
        </script>
    @endsection
@endsection