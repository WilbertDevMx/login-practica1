@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('head')
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<style>
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem;
        width: 100%;
        max-width: 420px;
    }
    h2 { color: #333; margin-bottom: 0.3rem; text-align: center; font-size: 1.6rem; }
    .subtitle { text-align: center; color: #888; font-size: 0.85rem; margin-bottom: 1.5rem; }
    label { display: block; margin-top: 1rem; color: #555; font-size: 0.88rem; font-weight: 600; }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%; padding: 10px 12px; margin-top: 4px;
        border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;
        transition: border-color 0.2s;
    }
    input:focus { outline: none; border-color: #667eea; }
    .error-msg { color: #e74c3c; font-size: 0.82rem; margin-top: 4px; }
    .password-strength {
        margin-top: 8px; height: 6px; border-radius: 3px;
        background: #eee; overflow: hidden;
    }
    .password-strength-bar {
        height: 100%; width: 0%; border-radius: 3px;
        transition: width 0.3s, background 0.3s;
    }
    .strength-label { font-size: 0.78rem; margin-top: 4px; color: #888; }
    .requirements {
        background: #f7f9fc; border-radius: 8px;
        padding: 10px 14px; margin-top: 8px; font-size: 0.8rem;
    }
    .req { display: flex; align-items: center; gap: 6px; margin: 3px 0; color: #aaa; }
    .req.valid { color: #27ae60; }
    .req.valid::before { content: '✅'; }
    .req:not(.valid)::before { content: '❌'; }
    button[type="submit"] {
        width: 100%; margin-top: 1.5rem; padding: 12px;
        background: #667eea; color: white; border: none;
        border-radius: 8px; font-size: 1rem; font-weight: bold;
        cursor: pointer; transition: background 0.2s;
    }
    button[type="submit"]:hover { background: #5563c1; }
    button[type="submit"]:disabled { background: #aaa; cursor: not-allowed; }
    .login-link { text-align: center; margin-top: 1rem; font-size: 0.88rem; color: #888; }
    .login-link a { color: #667eea; text-decoration: none; font-weight: 600; }
    .login-link a:hover { text-decoration: underline; }
</style>
@endsection

@section('content')
<div class="card">
    <h2>Crear cuenta</h2>
    <p class="subtitle">Completa el formulario para registrarte</p>

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <label>Nombre completo:</label>
        <input type="text" name="name" value="{{ old('name') }}"
               maxlength="100" required autocomplete="name">
        @error('name')
            <div class="error-msg">{{ $message }}</div>
        @enderror

        <label>Correo electrónico:</label>
        <input type="email" name="email" value="{{ old('email') }}"
               maxlength="255" required autocomplete="email">
        @error('email')
            <div class="error-msg">{{ $message }}</div>
        @enderror

        <label>Contraseña:</label>
        <input type="password" name="password" id="password"
               maxlength="128" required autocomplete="new-password">
        <div class="password-strength">
            <div class="password-strength-bar" id="strengthBar"></div>
        </div>
        <div class="strength-label" id="strengthLabel">Ingresa una contraseña</div>
        <div class="requirements">
            <div class="req" id="req-len">Mínimo 12 caracteres</div>
            <div class="req" id="req-upper">Al menos una mayúscula</div>
            <div class="req" id="req-lower">Al menos una minúscula</div>
            <div class="req" id="req-num">Al menos un número</div>
            <div class="req" id="req-sym">Al menos un símbolo (@$!%*?&_-#)</div>
        </div>
        @error('password')
            <div class="error-msg">{{ $message }}</div>
        @enderror

        <label>Confirmar contraseña:</label>
        <input type="password" name="password_confirmation" id="passwordConfirm"
               maxlength="128" required autocomplete="new-password">
        <div class="error-msg" id="matchError" style="display:none;">Las contraseñas no coinciden.</div>
        @error('password_confirmation')
            <div class="error-msg">{{ $message }}</div>
        @enderror

        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

        <button type="submit" id="submitBtn" disabled>Crear cuenta</button>
    </form>

    <div class="login-link">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('passwordConfirm');
    const strengthBar    = document.getElementById('strengthBar');
    const strengthLabel  = document.getElementById('strengthLabel');
    const submitBtn      = document.getElementById('submitBtn');
    const matchError     = document.getElementById('matchError');

    const reqs = {
        len:   { el: document.getElementById('req-len'),   fn: p => p.length >= 12 },
        upper: { el: document.getElementById('req-upper'), fn: p => /[A-Z]/.test(p) },
        lower: { el: document.getElementById('req-lower'), fn: p => /[a-z]/.test(p) },
        num:   { el: document.getElementById('req-num'),   fn: p => /\d/.test(p) },
        sym:   { el: document.getElementById('req-sym'),   fn: p => /[@$!%*?&_\-#]/.test(p) },
    };

    function checkPassword() {
        const p = passwordInput.value;
        let score = 0;

        Object.values(reqs).forEach(function(req) {
            const valid = req.fn(p);
            req.el.classList.toggle('valid', valid);
            if (valid) score++;
        });

        const pct    = (score / 5) * 100;
        const colors = ['#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#27ae60'];
        const labels = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];

        strengthBar.style.width      = pct + '%';
        strengthBar.style.background = colors[score - 1] || '#eee';
        strengthLabel.textContent    = score > 0 ? labels[score - 1] : 'Ingresa una contraseña';

        checkMatch();
    }

    function checkMatch() {
        const allValid   = Object.values(reqs).every(r => r.fn(passwordInput.value));
        const match      = passwordInput.value === confirmInput.value && confirmInput.value !== '';
        matchError.style.display = confirmInput.value && !match ? 'block' : 'none';
        submitBtn.disabled = !(allValid && match);
    }

    passwordInput.addEventListener('input', checkPassword);
    confirmInput.addEventListener('input', checkMatch);

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerText = 'Registrando...';

        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'register' }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
                document.getElementById('registerForm').submit();
            });
        });
    });

    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Crear cuenta';
        }
    });
</script>
@endsection