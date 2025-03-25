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
                    <h5 class="card-header">Register New Service</h5>
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

                        <form method="POST" action="{{ route('services.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Description</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ old('descripcion') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ old('precio') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="acreditado" class="form-label">Accredited</label>
                                <input type="checkbox" id="acreditado" name="acreditado" value="1" {{ old('acreditado') ? 'checked' : '' }}>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                            <a href="{{ route('listas') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
                <a href="{{ url('/gestion_calidad') }}" class="back-btn">Back to Dashboard</a>
            </div>
        </center>
    </div>

    @section('scripts')
        <!-- SweetAlert2 for error messages -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endsection
@endsection