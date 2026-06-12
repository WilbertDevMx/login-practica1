<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RoleChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            if (Auth::user()->getRoleNames()->first() !== 'administrador') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'rol' => ['required', 'string', 'exists:roles,name'],
        ]);

        // No permitir cambiar el propio rol
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        $admin       = Auth::user();
        $rolAnterior = $user->getRoleNames()->first() ?? 'sin rol';
        $rolNuevo    = $request->rol;

        // Evitar log innecesario si el rol es el mismo
        if ($rolAnterior === $rolNuevo) {
            return back()->with('error', 'El usuario ya tiene ese rol.');
        }

        $user->syncRoles([$rolNuevo]);

        RoleChangeLog::create([
            'admin_id'       => $admin->id,
            'admin_email'    => $admin->email,
            'target_user_id' => $user->id,
            'target_email'   => $user->email,
            'rol_anterior'   => $rolAnterior,
            'rol_nuevo'      => $rolNuevo,
            'ip'             => $request->ip(),
        ]);

        return back()->with('success', "Rol de {$user->email} cambiado de '{$rolAnterior}' a '{$rolNuevo}'.");
    }
}