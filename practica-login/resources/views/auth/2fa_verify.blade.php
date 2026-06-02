@extends('layouts.app')

@section('title', 'Verificación de Dos Factores')

@section('head')
<style>
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem;
        width: 100%;
        max-width: 420px;
        text-align: center;
    }
    h2 { color: #333; margin-bottom: 1rem; }
    p { color: #555; margin-bottom: 1rem; font-size: 0.95rem; }
    .qr-box { margin: 1rem auto; }
    .qr-box img { max-width: 180px; border: 4px solid #667eea; border-radius: 8px; }
    .secret-box {
        background: #f0f4ff; border: 1px dashed #667eea;
        border-radius: 8px; padding: 10px 16px;
        font-family: monospace; font-size: 1rem;
        color: #333; margin: 0.5rem 0 1.5rem;
        word-break: break-all;
    }
    input[type="text"] {
        width: 100%; padding: 12px; border: 1px solid #ddd;
        border-radius: 8px; font-size: 1.2rem;
        text-align: center; letter-spacing: 6px;
        margin-top: 0.5rem;
    }
    button[type="submit"] {
        width: 100%; margin-top: 1.2rem; padding: 12px;
        background: #667eea; color: white; border: none;
        border-radius: 8px; font-size: 1rem; font-weight: bold;
        cursor: pointer; transition: background 0.2s;
    }
    button[type="submit"]:hover { background: #5563c1; }
    .error { color: red; font-size: 0.9rem; margin-top: 8px; }
</style>
@endsection

@section('content')
<div class="card">
    <h2>🔐 Verificación 2FA</h2>

    @if($isNew)
        <p>Escanea este código QR con <strong>Google Authenticator</strong>:</p>
        <div class="qr-box">
            <img src="{{ $qrCode }}" alt="Código QR para Google Authenticator">
        </div>
        <p>O introduce manualmente esta clave secreta:</p>
        <div class="secret-box">{{ $secret }}</div>
    @endif

    <p>Ingresa el código de 6 dígitos de tu aplicación autenticadora:</p>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf
        <input type="text" name="one_time_password"
               placeholder="000000" maxlength="6"
               required autofocus autocomplete="one-time-code">
        @error('one_time_password')
            <div class="error">{{ $message }}</div>
        @enderror
        <button type="submit">Verificar</button>
    </form>
</div>
@endsection