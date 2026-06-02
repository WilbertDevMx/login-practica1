<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Dos Factores</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h2>Verificación de Dos Factores</h2>

    @if($isNew)
        <p>Escanea este código QR con Google Authenticator:</p>
        <img src="{{ $qrCode }}" alt="Código QR para Google Authenticator">
        <p>O introduce manualmente esta clave secreta: <strong>{{ $secret }}</strong></p>
    @endif

    <p>Ingresa el código de 6 dígitos de tu aplicación autenticadora:</p>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf
        <input type="text" name="one_time_password" placeholder="000000" required autofocus>
        @error('one_time_password')
            <div style="color:red;">{{ $message }}</div>
        @enderror
        <button type="submit">Verificar</button>
    </form>
</body>
</html>