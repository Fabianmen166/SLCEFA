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
                    <h5 class="card-header">List of Services</h5>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="GET" action="{{ route('listas') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="descripcion" class="form-label">Search by Description</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ request('descripcion') }}" placeholder="Service description">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('listas') }}" class="btn btn-secondary ms-2">Clear</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Price</th>
                                        <th>Accredited</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($services as $item)
                                        <tr>
                                            <td>{{ $item->descripcion }}</td>
                                            <td>{{ number_format($item->precio, 2) }}</td>
                                            <td>{{ $item->acreditado ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-success editbtn"
                                                        data-id="{{ $item->services_id }}"
                                                        data-descripcion="{{ $item->descripcion }}"
                                                        data-precio="{{ $item->precio }}"
                                                        data-acreditado="{{ $item->acreditado }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editar">Edit</button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger deletebtn"
                                                        data-id="{{ $item->services_id }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#eliminar">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No services registered.</td>
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
                                            <h5 class="modal-title" id="editarLabel">Edit Service</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" id="edit-id" name="id">
                                            <div class="mb-3">
                                                <label for="edit-descripcion" class="form-label">Description</label>
                                                <input type="text" class="form-control" id="edit-descripcion" name="descripcion" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-precio" class="form-label">Price</label>
                                                <input type="number" step="0.01" class="form-control" id="edit-precio" name="precio" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-acreditado" class="form-label">Accredited</label>
                                                <input type="checkbox" id="edit-acreditado" name="acreditado" value="1">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update</button>
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
                                            <h5 class="modal-title" id="eliminarLabel">Delete Service</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" id="delete-id">
                                            <p>Are you sure you want to delete this service?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ url('/gestion_calidad') }}" class="back-btn">Back to Dashboard</a>
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
                        document.getElementById('formEditar').action = `/services/${id}`;
                        document.getElementById('edit-id').value = id || '';
                        document.getElementById('edit-descripcion').value = this.getAttribute('data-descripcion') || '';
                        document.getElementById('edit-precio').value = this.getAttribute('data-precio') || '';
                        const acreditado = this.getAttribute('data-acreditado') === '1';
                        document.getElementById('edit-acreditado').checked = acreditado;
                    });
                });

                // Delete
                document.querySelectorAll('.deletebtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        document.getElementById('formEliminar').action = `/services/${id}`;
                        document.getElementById('delete-id').value = id;
                    });
                });
            });
        </script>
    @endsection
@endsection