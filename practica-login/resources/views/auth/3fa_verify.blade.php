<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Tres Factores</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h2>Verificación de Tres Factores</h2>

    @if(session('status'))
        <div style="color:green;">{{ session('status') }}</div>
    @endif

    <p>Hemos enviado un código de verificación a tu correo electrónico. Por favor, ingrésalo a continuación:</p>

    <form method="POST" action="{{ route('3fa.verify') }}">
        @csrf
        <input type="text" name="verification_code" placeholder="Código de 6 caracteres" required autofocus>
        @error('verification_code')
            <div style="color:red;">{{ $message }}</div>
        @enderror
        <button type="submit">Verificar</button>
    </form>

    @if(session('resend'))
        <form method="POST" action="{{ route('2fa.resend') }}" style="margin-top: 10px;">
            @csrf
            <button type="submit">Reenviar código</button>
        </form>
    @endif
</body>
</html>