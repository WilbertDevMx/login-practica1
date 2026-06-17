@extends('layouts.app')

@section('title', 'Verificación de Tres Factores')

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
    .btn-resend {
        width: 100%; margin-top: 0.8rem; padding: 10px;
        background: transparent; color: #667eea;
        border: 1px solid #667eea; border-radius: 8px;
        font-size: 0.9rem; cursor: pointer; transition: all 0.2s;
    }
    .btn-resend:hover { background: #f0f4ff; }
    .error { color: red; font-size: 0.9rem; margin-top: 8px; }
    .alert-success {
        background: #d4edda; color: #155724;
        padding: 10px; border-radius: 8px;
        margin-bottom: 1rem; border-left: 4px solid #28a745;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="card">
    <h2> Verificación 3FA</h2>

    @if(session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <p>Hemos enviado un código de verificación a tu correo electrónico. Por favor, ingrésalo a continuación:</p>

    <form method="POST" action="{{ route('3fa.verify') }}">
        @csrf
        <input type="text" name="verification_code"
               placeholder="ABC123" maxlength="6"
               required autofocus autocomplete="one-time-code">
        @error('verification_code')
            <div class="error">{{ $message }}</div>
        @enderror
        <button type="submit">Verificar</button>
    </form>

    @if(session('resend'))
        <form method="POST" action="{{ route('3fa.resend') }}" style="margin-top: 10px;">
            @csrf
            <button type="submit" class="btn-resend">Reenviar código</button>
        </form>
    @endif
</div>
@endsection