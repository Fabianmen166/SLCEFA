@extends('layouts.plantilla')
@section('contenido')
<br><br>
<center>
    <div class="card" style="width: 50rem" ;>
        <h5 class="card-header">Registrar Usuario</h5>
        <div class="card-body">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Rol</label>
                    <select class="form-control" id="role" name="role">
                        <option value="personal_tecnico">Personal Tecnico</option>
                        <option value="gestion_calidad">Gestion Calidad</option>
                        <option value="pasante">Pasante</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </form>
        </div>
    </div>
</center>
@endsection
