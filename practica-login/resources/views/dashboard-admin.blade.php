@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('head')
<style>
    body { align-items: flex-start; padding: 40px 20px; }
    .container { width: 100%; max-width: 960px; }
    .card {
        background: white; border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 2rem; margin-bottom: 1.5rem;
    }
    h1 { color: #333; margin-bottom: 0.5rem; }
    h2 { color: #333; margin-bottom: 1rem; font-size: 1.2rem; margin-top: 0; }
    .subtitle { color: #888; font-size: 0.9rem; margin-bottom: 1.5rem; }
    .logout-btn {
        background: #e74c3c; color: white; border: none;
        padding: 10px 20px; border-radius: 8px;
        font-size: 0.95rem; font-weight: bold; cursor: pointer;
        transition: background 0.2s;
    }
    .logout-btn:hover { background: #c0392b; }
    table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    th { background: #667eea; color: white; padding: 10px 12px; text-align: left; }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; color: #333; vertical-align: middle; }
    tr:hover td { background: #f7f9fc; }
    .badge {
        display: inline-block; padding: 3px 10px;
        border-radius: 12px; font-size: 0.78rem; font-weight: bold;
    }
    .badge-ok      { background: #d4edda; color: #155724; }
    .badge-fail    { background: #f8d7da; color: #721c24; }
    .badge-role    { background: #e8f0fe; color: #1a56db; }
    .alert-success {
        background: #d4edda; color: #155724; padding: 10px;
        border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #28a745;
    }
    .alert-error {
        background: #f8d7da; color: #721c24; padding: 10px;
        border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #e74c3c;
    }
    .role-select {
        padding: 5px 8px; border: 1px solid #ddd; border-radius: 6px;
        font-size: 0.82rem; color: #333; background: white;
    }
    .role-btn {
        padding: 5px 12px; background: #667eea; color: white;
        border: none; border-radius: 6px; font-size: 0.82rem;
        cursor: pointer; transition: background 0.2s; margin-left: 4px;
    }
    .role-btn:hover { background: #5563c1; }
    .text-muted { color: #aaa; font-size: 0.8rem; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- Header --}}
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
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif
    </div>

    {{-- Gestión de usuarios y roles --}}
    <div class="card">
        <h2> Usuarios y roles</h2>
        @if($users->isEmpty())
            <p class="text-muted">No hay usuarios registrados.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol actual</th>
                        <th>Registrado</th>
                        <th>Cambiar rol</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge badge-role">{{ $u->getRoleNames()->first() ?? 'sin rol' }}</span></td>
                        <td>{{ $u->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($u->id !== Auth::id())
                                <form method="POST" action="{{ route('admin.roles.update', $u) }}" style="display:flex; align-items:center; gap:4px;">
                                    @csrf
                                    @method('PUT')
                                    <select name="rol" class="role-select">
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->name }}"
                                                {{ $u->getRoleNames()->first() === $rol->name ? 'selected' : '' }}>
                                                {{ ucfirst($rol->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="role-btn">Guardar</button>
                                </form>
                            @else
                                <span class="text-muted">Tú</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Logs de login --}}
    <div class="card">
        <h2> Últimos 50 intentos de login</h2>
        @if($loginLogs->isEmpty())
            <p class="text-muted">No hay registros aún.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Lugar</th><th>Email</th><th>IP</th><th>Estado</th><th>Navegador</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    @foreach($loginLogs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td> {{$log-> error_en}}</td>
                        <td>{{ $log->email }}</td>
                        <td>{{ $log->ip }}</td>
                        <td>
                            <span class="badge {{ $log->exitoso ? 'badge-ok' : 'badge-fail' }}">
                                {{ $log->exitoso ? ' Exitoso' : ' Fallido' }}
                            </span>
                        </td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $log->user_agent }}</td>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Logs de registro --}}
    <div class="card">
        <h2> Últimos 50 intentos de registro</h2>
        @if($registrationLogs->isEmpty())
            <p class="text-muted">No hay registros aún.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Email</th><th>IP</th><th>Estado</th><th>Mensaje</th><th>Navegador</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    @foreach($registrationLogs as $regLog)
                    <tr>
                        <td>{{ $regLog->id }}</td>
                        <td>{{ $regLog->email }}</td>
                        <td>{{ $regLog->ip }}</td>
                        <td>
                            <span class="badge {{ $regLog->successful ? 'badge-ok' : 'badge-fail' }}">
                                {{ $regLog->successful ? ' Exitoso' : ' Fallido' }}
                            </span>
                        </td>
                        <td style="max-width:200px; word-break:break-word;">{{ $regLog->message ?? '-' }}</td>
                        <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $regLog->user_agent }}</td>
                        <td>{{ $regLog->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Logs de cambio de rol --}}
    <div class="card">
        <h2> Últimos 50 cambios de rol</h2>
        @if($roleChangeLogs->isEmpty())
            <p class="text-muted">No hay cambios de rol registrados.</p>
        @else
            <table>
                <thead>
                    <tr><th>#</th><th>Admin</th><th>Usuario</th><th>Rol anterior</th><th>Rol nuevo</th><th>IP</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    @foreach($roleChangeLogs as $rlog)
                    <tr>
                        <td>{{ $rlog->id }}</td>
                        <td>{{ $rlog->admin_email }}</td>
                        <td>{{ $rlog->target_email }}</td>
                        <td><span class="badge badge-fail">{{ $rlog->rol_anterior }}</span></td>
                        <td><span class="badge badge-ok">{{ $rlog->rol_nuevo }}</span></td>
                        <td>{{ $rlog->ip }}</td>
                        <td>{{ $rlog->created_at->format('d/m/Y H:i:s') }}</td>
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