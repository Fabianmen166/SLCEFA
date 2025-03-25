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
                    <h5 class="card-header">Upload Proof of Payment for Quote #{{ $quote->quote_id }}</h5>
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

                        <form method="POST" action="{{ route('quote.upload.file', $quote->quote_id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Proof of Payment (PDF, JPG, PNG)</label>
                                <input type="file" class="form-control" id="archivo" name="archivo" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                            <a href="{{ route('lista') }}" class="btn btn-secondary">Cancel</a>
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