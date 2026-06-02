@extends('layouts.app')

@section('title', 'Dashboard Usuario')

@section('head')
<style>
    body { align-items: flex-start; padding: 40px 20px; }
    .container { width: 100%; max-width: 700px; }
    .card {
        background: white; border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem; margin-bottom: 1.2rem;
    }
    .header-row {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 1.5rem;
    }
    .avatar {
        background: #667eea; color: white;
        width: 60px; height: 60px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: bold;
    }
    .user-name h1 { color: #333; font-size: 1.4rem; margin-bottom: 2px; }
    .badge-rol {
        display: inline-block; background: #e8f5e9; color: #2e7d32;
        padding: 3px 12px; border-radius: 12px; font-size: 0.78rem;
        font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
    }
    .logout-btn {
        background: #e74c3c; color: white; border: none;
        padding: 10px 20px; border-radius: 8px; font-size: 0.9rem;
        font-weight: bold; cursor: pointer; transition: background 0.2s;
    }
    .logout-btn:hover { background: #c0392b; }
    .stats-grid {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 1rem; margin-bottom: 1.2rem;
    }
    .stat-card {
        background: #f7f9fc; border-radius: 12px;
        padding: 1.2rem; text-align: center;
    }
    .stat-card .number { font-size: 2rem; font-weight: bold; color: #667eea; }
    .stat-card .label { font-size: 0.8rem; color: #888; margin-top: 4px; }
    .section-title { color: #333; font-size: 1rem; font-weight: bold; margin-bottom: 1rem; }
    .feature-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .feature-item {
        background: #f7f9fc; border-radius: 12px;
        padding: 1rem; cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #eee;
    }
    .feature-item:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .feature-item .icon { font-size: 1.8rem; margin-bottom: 8px; }
    .feature-item h3 { color: #333; font-size: 0.95rem; margin-bottom: 4px; }
    .feature-item p { color: #888; font-size: 0.8rem; }
    .user-info { margin-bottom: 0; }
    .user-info p { margin: 8px 0; color: #2c3e50; font-size: 0.95rem; }
    .user-info strong { color: #667eea; }
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

    {{-- Header --}}
    <div class="card">
        <div class="header-row">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="user-name">
                    <h1>{{ Auth::user()->name }}</h1>
                    <span class="badge-rol"> Usuario</span>
                </div>
            </div>
            <form method="POST" action="/cerrar-sesion-ahora">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="user-info">
            <p><strong> Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong> Miembro desde:</strong> {{ Auth::user()->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number">12</div>
            <div class="label">Documentos</div>
        </div>
        <div class="stat-card">
            <div class="number">3</div>
            <div class="label">Tareas pendientes</div>
        </div>
        <div class="stat-card">
            <div class="number">98%</div>
            <div class="label">Perfil completo</div>
        </div>
    </div>

    {{-- Funciones --}}
    <div class="card">
        <div class="section-title"> Accesos rápidos</div>
        <div class="feature-grid">
            <div class="feature-item">
                <div class="icon"></div>
                <h3>Mi Perfil</h3>
                <p>Edita tu información personal y foto.</p>
            </div>
            <div class="feature-item">
                <div class="icon"></div>
                <h3>Mis Archivos</h3>
                <p>Accede y gestiona tus documentos.</p>
            </div>
            <div class="feature-item">
                <div class="icon"></div>
                <h3>Mis Reportes</h3>
                <p>Visualiza tu actividad reciente.</p>
            </div>
            <div class="feature-item">
                <div class="icon"></div>
                <h3>Notificaciones</h3>
                <p>Revisa tus alertas y mensajes.</p>
            </div>
        </div>
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