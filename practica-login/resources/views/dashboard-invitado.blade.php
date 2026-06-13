@extends('layouts.app')

@section('title', 'Dashboard Invitado')

@section('head')
<style>
    body { align-items: center; }
    .container { width: 100%; max-width: 500px; }
    .card {
        background: white; border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem; margin-bottom: 1.2rem;
        text-align: center;
    }
    .avatar {
        background: #b0bec5; color: white;
        width: 80px; height: 80px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: bold; margin: 0 auto 1rem;
    }
    h1 { color: #333; margin-bottom: 0.3rem; font-size: 1.8rem; }
    .badge-rol {
        display: inline-block; background: #eceff1; color: #607d8b;
        padding: 4px 14px; border-radius: 12px; font-size: 0.8rem;
        font-weight: bold; margin-bottom: 1rem; text-transform: uppercase;
        letter-spacing: 1px;
    }
    .user-info {
        background: #f7f9fc; padding: 1rem;
        border-radius: 12px; text-align: left; margin-bottom: 1rem;
    }
    .user-info p { margin: 8px 0; color: #2c3e50; font-size: 0.95rem; }
    .user-info strong { color: #667eea; }
    .alert-info {
        background: #fff3cd; color: #856404;
        padding: 12px 16px; border-radius: 10px;
        border-left: 4px solid #ffc107;
        font-size: 0.9rem; text-align: left; margin-bottom: 1rem;
    }
    .card-feature {
        background: white; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.07);
        padding: 1.2rem; margin-bottom: 1rem;
        text-align: left; opacity: 0.5;
        position: relative; overflow: hidden;
    }
    .card-feature h3 { color: #333; margin-bottom: 0.3rem; font-size: 1rem; }
    .card-feature p { color: #888; font-size: 0.85rem; }
    .lock-badge {
        position: absolute; top: 12px; right: 12px;
        background: #eceff1; color: #90a4ae;
        padding: 2px 8px; border-radius: 8px; font-size: 0.75rem;
    }
    .logout-btn {
        background: #e74c3c; color: white; border: none;
        padding: 12px; border-radius: 8px; font-size: 1rem;
        font-weight: bold; cursor: pointer; width: 100%;
        transition: background 0.2s;
    }
    .logout-btn:hover { background: #c0392b; }
    .alert-success {
        background: #d4edda; color: #155724; padding: 10px;
        border-radius: 8px; margin-bottom: 1rem;
        border-left: 4px solid #28a745; font-size: 0.9rem;
    }
    .footer { text-align: center; font-size: 0.75rem; color: #95a5a6; margin-top: 0.5rem; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        <h1>Bienvenido, {{ Auth::user()->name }}</h1>
        <span class="badge-rol"> Invitado</span>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="user-info">
            <p><strong> Nombre:</strong> {{ Auth::user()->name }}</p>
            <p><strong> Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong> Miembro desde:</strong> {{ Auth::user()->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="alert-info">
             Tu cuenta tiene acceso limitado. Contacta al administrador para obtener más permisos.
        </div>

        <form method="POST" action="/cerrar-sesion-ahora">
            @csrf
            <button type="submit" class="logout-btn">Cerrar sesión</button>
        </form>
    </div>

    {{-- Funciones bloqueadas --}}
    <div class="card-feature">
        <span class="lock-badge"> Sin acceso</span>
        <h3> Reportes</h3>
        <p>Visualiza estadísticas y reportes del sistema.</p>
    </div>
    <div class="card-feature">
        <span class="lock-badge"> Sin acceso</span>
        <h3> Configuración</h3>
        <p>Ajusta preferencias y parámetros de tu cuenta.</p>
    </div>
    <div class="card-feature">
        <span class="lock-badge"> Sin acceso</span>
        <h3> Archivos</h3>
        <p>Accede y gestiona documentos del sistema.</p>
    </div>

    <div class="footer">&copy; {{ date('Y') }} - Aplicación con buenas prácticas de seguridad</div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) { window.location.reload(); }
    });
</script>
@endsection