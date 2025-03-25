<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laboratorio</title>

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
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .title {
            font-size: 3.5rem;
            font-weight: 700;
            color: #2a2a72;
            margin-bottom: 1rem;
            animation: fadeIn 1.5s ease-in-out;
        }

        .subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            color: #4a4a8a;
            margin-bottom: 2rem;
            animation: fadeIn 2s ease-in-out;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 500;
            color: #fff;
            background: #2a2a72;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: slideUp 2.5s ease-in-out;
        }

        .btn:hover {
            background: #4a4a8a;
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .background-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            left: 70%;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 40%;
            left: 30%;
            animation-delay: 10s;
        }

        /* Animaciones */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-30px); }
            100% { transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .title {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }

            .buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <h1 class="title">Laboratorio</h1>
        <h1>Laboratorio Cefa</h1>
        <p class="subtitle">Tu espacio para crear y descubrir</p>
        <div class="buttons">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('login') }}" class="btn">Entrar</a>
                @else
                    <a href="{{ route('login') }}" class="btn">Iniciar Sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn">Registrarse</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</body>
</html>