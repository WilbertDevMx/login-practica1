<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Seguro</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; }
        .alert-success { color: green; margin-top: 10px; }
    </style>
    <!-- Cargar script de reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
</head>
<body>
    <h2>Iniciar sesión</h2>

    @if ($errors->any())
        <div class="error">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('message'))
        <div class="alert-success">
            {{ session('message') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div>
            <label>Contraseña:</label>
            <input type="password" name="password" required>
        </div>

        <!-- Campo oculto donde se almacenará el token de reCAPTCHA -->
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <div>
            <label>
                <input type="checkbox" name="remember"> Recordarme
            </label>
        </div>

        <button type="submit" id="submitBtn">Entrar</button>
    </form>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Evita el envío normal

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Verificando...';

            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'login' }).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.getElementById('loginForm').submit();
                });
            });
        });
    </script>
</body>
</html>