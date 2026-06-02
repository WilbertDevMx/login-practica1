@extends('layouts.app')

@section('title', 'Login Seguro')

@section('head')
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<style>
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem;
        width: 100%;
        max-width: 400px;
    }
    h2 { color: #333; margin-bottom: 1.5rem; text-align: center; }
    label { display: block; margin-top: 1rem; color: #555; font-size: 0.9rem; }
    input[type="email"],
    input[type="password"] {
        width: 100%; padding: 10px 12px; margin-top: 4px;
        border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;
    }
    .remember { display: flex; align-items: center; gap: 8px; margin-top: 1rem; color: #555; font-size: 0.9rem; }
    button[type="submit"] {
        width: 100%; margin-top: 1.5rem; padding: 12px;
        background: #667eea; color: white; border: none;
        border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer;
        transition: background 0.2s;
    }
    button[type="submit"]:hover { background: #5563c1; }
    button[type="submit"]:disabled { background: #aaa; cursor: not-allowed; }
    .error { color: red; margin-bottom: 10px; font-size: 0.9rem; }
    .alert-success { color: green; margin-top: 10px; font-size: 0.9rem; }
</style>
@endsection

@section('content')
<div class="card">
    <h2>Iniciar sesión</h2>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    @if(session('message'))
        <div class="alert-success">{{ session('message') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <label>Email:</label>
        <input type="email" name="email" value="{{ old('email') }}"
               maxlength="255" required autofocus autocomplete="email">

        <label>Contraseña:</label>
        <input type="password" name="password"
               maxlength="128" required autocomplete="current-password">

        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <div class="remember">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember" style="margin:0;">Recordarme</label>
        </div>

        <button type="submit" id="submitBtn">Entrar</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerText = 'Verificando...';

        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'login' }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
                document.getElementById('loginForm').submit();
            });
        });
    });

    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = false;
            btn.innerText = 'Entrar';
        }
    });
</script>
@endsection