@extends('layouts.app')

<center><h1>bienvenido instructor</h1> </center>
@section('content')
<div class="card-body">
    <div class="mb-4">
        <a href="{{ route('phosphorus_analysis.index') }}" class="btn btn-primary m-1">Fósforo</a>
        <a href="{{ route('boron_analysis.index') }}" class="btn btn-info m-1">Boro</a>
        <a href="{{ route('sulfur_analysis.index') }}" class="btn btn-warning m-1">Azufre</a>
        <!-- Agrega aquí más servicios si lo deseas -->
    </div>
    @if(session('error'))
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