<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Iniciar Sesión - Laboratorio</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f0fa 0%, #d1e8f5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .lab-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.1;
            pointer-events: none;
        }

        .lab-background svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: #2a2a72;
            stroke-width: 1;
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .login-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            animation: fadeIn 1s ease-in-out;
        }

        .title {
            font-size: 2rem;
            font-weight: 700;
            color: #2a2a72;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            font-weight: 300;
            color: #4a4a8a;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 1rem;
            color: #2a2a72;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #cfdef3;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2a2a72;
            box-shadow: 0 0 5px rgba(42, 42, 114, 0.2);
        }

        .is-invalid {
            border-color: #e94560;
        }

        .invalid-feedback {
            color: #e94560;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }

        .checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .form-check-input {
            margin-right: 0.5rem;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #4a4a8a;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            background: #2a2a72;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background: #4a4a8a;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-link {
            font-size: 0.9rem;
            color: #2a2a72;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .btn-link:hover {
            color: #4a4a8a;
            text-decoration: underline;
        }

        .back-btn {
            display: block;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #4a4a8a;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-btn:hover {
            color: #2a2a72;
            text-decoration: underline;
        }

        /* Animaciones */
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 1.5rem;
                max-width: 90%;
            }

            .title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 0.9rem;
            }

            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo temático de laboratorio -->
    <div class="lab-background">
        <svg viewBox="0 0 1000 1000" preserveAspectRatio="none">
            <!-- Elementos alusivos a laboratorio: frascos, líneas curvas -->
            <path d="M100 200 Q200 300 300 200 T500 200 Q600 100 700 200 T900 200" stroke-linecap="round" />
            <path d="M150 400 Q250 500 350 400 T550 400 Q650 300 750 400 T950 400" stroke-linecap="round" />
            <circle cx="200" cy="600" r="50" />
            <path d="M200 550 V500 Q220 480 240 500 T260 550" stroke-linecap="round" />
            <circle cx="800" cy="700" r="40" />
            <path d="M800 660 V620 Q820 600 840 620 T860 660" stroke-linecap="round" />
        </svg>
    </div>

    <!-- Contenido -->
    <div class="container">
        <div class="login-card">
            <h2 class="title">Iniciar Sesión</h2>
            <p class="subtitle">Accede a tu espacio en el laboratorio</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group checkbox">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">{{ __('Login') }}</button>
                    @if (Route::has('password.request'))
                        <a class="btn-link" href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                    @endif
                </div>
            </form>

            <!-- Botón para volver al inicio -->
            <a href="{{ url('/') }}" class="back-btn">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>