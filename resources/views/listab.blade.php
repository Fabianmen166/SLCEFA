@extends('layouts.master1')

<center><h1>bienvenido instructor</h1> </center>
@section('content')
<center>  <img src="{{ asset('images/LogoAgrosoft2.png')}}" width="30%"></center>
<div class="card-body">
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Acceso Denegado',
            text: "{{ session('error') }}",
            confirmButtonText: 'Entendido'
        });
    </script>
      </div>
@endif
@endsection
@section('contenido')
<br><br>
<center>
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
</body>
</html>