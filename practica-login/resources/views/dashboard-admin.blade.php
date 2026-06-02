@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('head')
<style>
    body { align-items: flex-start; padding: 40px 20px; }
    .container { width: 100%; max-width: 900px; }
    .card {
        background: white; border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem; margin-bottom: 1.5rem;
    }
    h1 { color: #333; margin-bottom: 0.5rem; }
    .subtitle { color: #888; font-size: 0.9rem; margin-bottom: 1.5rem; }
    .logout-btn {
        background: #e74c3c; color: white; border: none;
        padding: 10px 20px; border-radius: 8px;
        font-size: 0.95rem; font-weight: bold;
        cursor: pointer; transition: background 0.2s;
    }
    .logout-btn:hover { background: #c0392b; }
    table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
    th {
        background: #667eea; color: white;
        padding: 10px 12px; text-align: left;
    }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; color: #333; }
    tr:hover td { background: #f7f9fc; }
    .badge {
        display: inline-block; padding: 3px 10px;
        border-radius: 12px; font-size: 0.78rem; font-weight: bold;
    }
    .badge-ok  { background: #d4edda; color: #155724; }
    .badge-fail { background: #f8d7da; color: #721c24; }
    .alert-success {
        background: #d4edda; color: #155724; padding: 10px;
        border-radius: 8px; margin-bottom: 1rem;
        border-left: 4px solid #28a745;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1> Panel de Administrador</h1>
                <div class="subtitle">Bienvenido, {{ Auth::user()->name }}</div>
            </div>
            <form method="POST" action="/cerrar-sesion-ahora">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
    </div>

    <div class="card">
        <h2 style="margin-bottom:1rem; color:#333;"> Últimos 50 intentos de login</h2>

        @if($logs->isEmpty())
            <p style="color:#888;">No hay registros aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Email</th>
                        <th>IP</th>
                        <th>Estado</th>
                        <th>Navegador</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->email }}</td>
                        <td>{{ $log->ip }}</td>
                        <td>
                            <span class="badge {{ $log->exitoso ? 'badge-ok' : 'badge-fail' }}">
                                {{ $log->exitoso ? ' Exitoso' : ' Fallido' }}
                            </span>
                        </td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $log->user_agent }}
                        </td>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) { window.location.reload(); }
    });
</script>
@endsection