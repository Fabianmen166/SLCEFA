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