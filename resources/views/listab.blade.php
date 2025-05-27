@extends('layouts.master1')

@section('content')
    <center>
        <h1>Bienvenido Instructor</h1>
        <img src="{{ asset('images/LogoAgrosoft2.png') }}" width="30%" alt="Logo Agrosoft">
    </center>

    <div class="card-body">
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso Denegado',
                    text: "{{ session('error') }}",
                    confirmButtonText: 'Entendido'
                });
            </script>
        @endif
    </div>
@endsection

@section('contenido')
    <style>
        /* Estilos generales */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Estilos para la tarjeta */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #3498db;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 15px;
            border-radius: 12px 12px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Estilos para la tabla */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }

        .table th {
            background-color: #ecf0f1;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .table td {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
        }

        .table tr:hover td {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        /* Estilos para los botones */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Estilos para los modales */
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #3498db;
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 15px;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px;
            border-top: 1px solid #e0e0e0;
        }

        /* Estilos para formularios */
        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }

        /* Estilos para alertas */
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* Estilos para el enlace "Volver al Inicio" */
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 10px;
            }

            h1 {
                font-size: 2rem;
            }

            .btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }
    </style>

    <div class="container">
        <div class="card">
            <h5 class="card-header">Lista de Usuarios</h5>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $item)
                                <tr>
                                    <td>{{ $item->user_id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->role }}</td>
                                    <td>
                                        <button type="button" class="btn btn-success editbtn"
                                                data-id="{{ $item->user_id }}"
                                                data-name="{{ $item->name }}"
                                                data-email="{{ $item->email }}"
                                                data-role="{{ $item->role }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editar">Editar</button>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger deletebtn"
                                                data-id="{{ $item->user_id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#eliminar">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No hay usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Editar -->
                <div class="modal fade" id="editar" tabindex="-1" aria-labelledby="editarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formEditar" method="POST" action="">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editarLabel">Editar Usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit-id">
                                    <div class="mb-3">
                                        <label for="edit-name" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="edit-name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="edit-email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-role" class="form-label">Rol</label>
                                        <select class="form-control" id="edit-role" name="role" required>
                                            <option value="personal_tecnico">Personal Técnico</option>
                                            <option value="gestion_calidad">Gestión Calidad</option>
                                            <option value="pasante">Pasante</option>
                                            <option value="admin">Admin</option>
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

                <!-- Modal Eliminar -->
                <div class="modal fade" id="eliminar" tabindex="-1" aria-labelledby="eliminarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formEliminar" method="POST" action="">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="eliminarLabel">Eliminar Usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="delete-id">
                                    <p>¿Estás seguro de que deseas eliminar este usuario?</p>
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
        <a href="{{ url('/admin') }}" class="back-btn">Volver al Inicio</a>
    </div>

    <!-- Bootstrap JS y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.editbtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('formEditar').action = `/usuarios/${id}`;
                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-name').value = this.getAttribute('data-name');
                    document.getElementById('edit-email').value = this.getAttribute('data-email');
                    document.getElementById('edit-role').value = this.getAttribute('data-role');
                });
            });

            document.querySelectorAll('.deletebtn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('formEliminar').action = `/usuarios/${id}`;
                    document.getElementById('delete-id').value = id;
                });
            });
        });
    </script>
@endsection