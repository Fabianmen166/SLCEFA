 <!-- Asegúrate de que 'layouts.app' coincida con tu layout principal -->

@section('contenido')
    <div class="lab-background">
        <svg viewBox="0 0 1000 1000" preserveAspectRatio="none">
            <path d="M100 200 Q200 300 300 200 T500 200 Q600 100 700 200 T900 200" stroke-linecap="round" />
            <path d="M150 400 Q250 500 350 400 T550 400 Q650 300 750 400 T950 400" stroke-linecap="round" />
            <circle cx="200" cy="600" r="50" />
            <path d="M200 550 V500 Q220 480 240 500 T260 550" stroke-linecap="round" />
            <circle cx="800" cy="700" r="40" />
            <path d="M800 660 V620 Q820 600 840 620 T860 660" stroke-linecap="round" />
        </svg>
    </div>

    <div class="container">
        <div class="card">
            <h5 class="card-header">Lista de Cotizaciones</h5>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre Persona</th>
                                <th>Nombre Empresa</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Estado de Pago</th>
                                <th>Detalles</th>
                                <th>Editar</th>
                                <th>Estado</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cotizaciones as $item)
                            <tr>
                                <td>{{ $item->nombre_persona }}</td>
                                <td>{{ $item->nombre_empresa ?? 'Sin empresa' }}</td>
                                <td>{{ $item->correo ?? 'Sin correo' }}</td>
                                <td>{{ $item->telefono }}</td>
                                <td>{{ $item->estado_de_pago }}</td>
                                <td>
                                    <button type="button" class="btn btn-warning detailbtn"
                                            data-id="{{ $item->id_cotizacion }}"
                                            data-nombre_persona="{{ $item->nombre_persona }}"
                                            data-nombre_empresa="{{ $item->nombre_empresa }}"
                                            data-nit="{{ $item->nit }}"
                                            data-direccion="{{ $item->direccion }}"
                                            data-telefono="{{ $item->telefono }}"
                                            data-correo="{{ $item->correo }}"
                                            data-precio="{{ $item->precio }}"
                                            data-fecha="{{ $item->fecha }}"
                                            data-estado_de_pago="{{ $item->estado_de_pago }}"
                                            data-archivo="{{ $item->archivo }}"
                                            data-user="{{ $item->user ? $item->user->name : 'Desconocido' }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detalles">Ver Detalles</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success editbtn"
                                            data-id="{{ $item->id_cotizacion }}"
                                            data-nombre_empresa="{{ $item->nombre_empresa }}"
                                            data-nombre_persona="{{ $item->nombre_persona }}"
                                            data-nit="{{ $item->nit }}"
                                            data-direccion="{{ $item->direccion }}"
                                            data-telefono="{{ $item->telefono }}"
                                            data-correo="{{ $item->correo }}"
                                            data-precio="{{ $item->precio }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editar">Editar</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info paymentbtn"
                                            data-id="{{ $item->id_cotizacion }}"
                                            data-id_cotizacion="{{ $item->id_cotizacion }}"
                                            data-estado_de_pago="{{ $item->estado_de_pago }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payment">Estado</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger deletebtn"
                                            data-id="{{ $item->id_cotizacion }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#eliminar">Eliminar</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9">No hay cotizaciones registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Detalles -->
                <div class="modal fade" id="detalles" tabindex="-1" aria-labelledby="detallesLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detallesLabel">Detalles de la Cotización</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>ID Cotización:</strong> <span id="detail-id"></span></p>
                                <p><strong>Nombre Persona:</strong> <span id="detail-nombre_persona"></span></p>
                                <p><strong>Nombre Empresa:</strong> <span id="detail-nombre_empresa"></span></p>
                                <p><strong>NIT:</strong> <span id="detail-nit"></span></p>
                                <p><strong>Dirección:</strong> <span id="detail-direccion"></span></p>
                                <p><strong>Teléfono:</strong> <span id="detail-telefono"></span></p>
                                <p><strong>Correo:</strong> <span id="detail-correo"></span></p>
                                <p><strong>Precio:</strong> <span id="detail-precio"></span></p>
                                <p><strong>Fecha:</strong> <span id="detail-fecha"></span></p>
                                <p><strong>Estado de Pago:</strong> <span id="detail-estado_de_pago"></span></p>
                                <p><strong>Archivo:</strong> <a id="detail-archivo" href="#" target="_blank">Ver</a></p>
                                <p><strong>Creado por:</strong> <span id="detail-user"></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar -->
                <div class="modal fade" id="editar" tabindex="-1" aria-labelledby="editarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formEditar" method="POST" action="">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editarLabel">Editar Cotización</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit-id">
                                    <div class="mb-3">
                                        <label for="edit-nombre_empresa" class="form-label">Nombre Empresa (Opcional)</label>
                                        <input type="text" class="form-control" id="edit-nombre_empresa" name="nombre_empresa">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-nombre_persona" class="form-label">Nombre Persona</label>
                                        <input type="text" class="form-control" id="edit-nombre_persona" name="nombre_persona" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-nit" class="form-label">NIT</label>
                                        <input type="text" class="form-control" id="edit-nit" name="nit" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="edit-direccion" name="direccion" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="edit-telefono" name="telefono" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-correo" class="form-label">Correo (Opcional)</label>
                                        <input type="email" class="form-control" id="edit-correo" name="correo">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-precio" class="form-label">Precio</label>
                                        <input type="number" step="0.01" class="form-control" id="edit-precio" name="precio" required>
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

                <!-- Modal Estado de Pago -->
                <div class="modal fade" id="payment" tabindex="-1" aria-labelledby="paymentLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="formPayment" method="POST" action="" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="paymentLabel">Actualizar Estado de Pago</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="payment-id">
                                    <div class="mb-3">
                                        <label for="payment-id_cotizacion" class="form-label">ID Cotización</label>
                                        <input type="text" class="form-control" id="payment-id_cotizacion" name="id_cotizacion" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment-estado_de_pago" class="form-label">Estado de Pago</label>
                                        <select class="form-control" id="payment-estado_de_pago" name="estado_de_pago" required>
                                            <option value="pendiente">Pendiente</option>
                                            <option value="pagado">Pagado</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment-archivo" class="form-label">Subir Archivo (máx. 2MB)</label>
                                        <input type="file" class="form-control" id="payment-archivo" name="archivo">
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
        <a href="{{ url('/admin') }}" class="back-btn">Volver al Inicio</a>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Detalles
                document.querySelectorAll('.detailbtn').forEach(button => {
                    button.addEventListener('click', function() {
                        document.getElementById('detail-id').textContent = this.getAttribute('data-id') || 'N/A';
                        document.getElementById('detail-nombre_persona').textContent = this.getAttribute('data-nombre_persona') || 'N/A';
                        document.getElementById('detail-nombre_empresa').textContent = this.getAttribute('data-nombre_empresa') || 'Sin empresa';
                        document.getElementById('detail-nit').textContent = this.getAttribute('data-nit') || 'N/A';
                        document.getElementById('detail-direccion').textContent = this.getAttribute('data-direccion') || 'N/A';
                        document.getElementById('detail-telefono').textContent = this.getAttribute('data-telefono') || 'N/A';
                        document.getElementById('detail-correo').textContent = this.getAttribute('data-correo') || 'Sin correo';
                        document.getElementById('detail-precio').textContent = this.getAttribute('data-precio') || 'N/A';
                        document.getElementById('detail-fecha').textContent = this.getAttribute('data-fecha') || 'N/A';
                        document.getElementById('detail-estado_de_pago').textContent = this.getAttribute('data-estado_de_pago') || 'N/A';
                        const archivo = this.getAttribute('data-archivo');
                        const archivoLink = document.getElementById('detail-archivo');
                        if (archivo) {
                            archivoLink.href = '{{ asset('storage/cotizaciones') }}/' + archivo;
                            archivoLink.textContent = archivo;
                        } else {
                            archivoLink.href = '#';
                            archivoLink.textContent = 'N/A';
                        }
                        document.getElementById('detail-user').textContent = this.getAttribute('data-user') || 'Desconocido';
                    });
                });

                // Editar
                document.querySelectorAll('.editbtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        document.getElementById('formEditar').action = `/cotizacion/${id}`;
                        document.getElementById('edit-id').value = id || '';
                        document.getElementById('edit-nombre_empresa').value = this.getAttribute('data-nombre_empresa') || '';
                        document.getElementById('edit-nombre_persona').value = this.getAttribute('data-nombre_persona') || '';
                        document.getElementById('edit-nit').value = this.getAttribute('data-nit') || '';
                        document.getElementById('edit-direccion').value = this.getAttribute('data-direccion') || '';
                        document.getElementById('edit-telefono').value = this.getAttribute('data-telefono') || '';
                        document.getElementById('edit-correo').value = this.getAttribute('data-correo') || '';
                        document.getElementById('edit-precio').value = this.getAttribute('data-precio') || '';
                    });
                });

                // Estado de Pago
                document.querySelectorAll('.paymentbtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        document.getElementById('formPayment').action = `/cotizacion/${id}/payment`;
                        document.getElementById('payment-id').value = id || '';
                        document.getElementById('payment-id_cotizacion').value = this.getAttribute('data-id_cotizacion') || '';
                        document.getElementById('payment-estado_de_pago').value = this.getAttribute('data-estado_de_pago') || 'pendiente';
                    });
                });

                // Eliminar
                document.querySelectorAll('.deletebtn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        document.getElementById('formEliminar').action = `/cotizacion/${id}`;
                        document.getElementById('delete-id').value = id || '';
                    });
                });
            });
        </script>
    @endpush
@endsection