<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ThreeFactorController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\LoginLog;
use App\Models\RegistrationLog;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí se definen las rutas web de la aplicación.
| Todas pertenecen al grupo middleware 'web'.
*/

// Rutas de autenticación (login, logout)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/cerrar-sesion-ahora', [LoginController::class, 'logout'])->name('logout.ahora');

// Dashboard principal (protegido)
//Route::get('/dashboard', function () {
  //  return view('dashboard');
//})->middleware('auth')->name('dashboard');

// Redirección raíz
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

// Registro
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('throttle:5,10');

// Cerrar sesión
Route::post('/cerrar-sesion-ahora', [LoginController::class, 'logout'])->name('logout.ahora');

// 2FA
Route::get('/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:3,3');

// 3FA
Route::get('/3fa/verify', [ThreeFactorController::class, 'showVerifyForm'])->name('3fa.verify')->middleware('throttle:3,10');
Route::post('/3fa/verify', [ThreeFactorController::class, 'verify'])->middleware('throttle:3,3');
Route::post('/3fa/resend', [ThreeFactorController::class, 'resendCode'])->name('3fa.resend');

// Dashboards
Route::get('/dashboard/invitado', function () {
    return view('dashboard-invitado');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.invitado');

Route::get('/dashboard/usuario', function () {
    return view('dashboard-usuario');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.usuario');

Route::get('/dashboard/admin', function () {
    return view('dashboard-admin', [
        'loginLogs'        => \App\Models\LoginLog::orderBy('created_at', 'desc')->take(50)->get(),
        'registrationLogs' => \App\Models\RegistrationLog::orderBy('created_at', 'desc')->take(50)->get(),
        'roleChangeLogs'   => \App\Models\RoleChangeLog::orderBy('created_at', 'desc')->take(50)->get(),
        'users'            => \App\Models\User::orderBy('created_at', 'desc')->get(),
        'roles'            => \Spatie\Permission\Models\Role::all(),
    ]);
})->middleware(['auth', 'mfa.complete'])->name('dashboard.admin');

// Cambio de rol (solo admin, verificado en el controlador)
Route::put('/admin/users/{user}/rol', [App\Http\Controllers\Admin\RoleController::class, 'update'])
    ->middleware(['auth'])
    ->name('admin.roles.update');
// Registro de usuarios
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,10');