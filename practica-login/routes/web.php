<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ThreeFactorController;
use App\Models\LoginLog;
use App\Models\RegistrationLog;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Mostrar formulario de login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Procesar login con rate limiting (5 intentos por minuto por IP)
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1');

// Cerrar sesión
Route::post('/cerrar-sesion-ahora', [LoginController::class, 'logout'])->name('logout.ahora');

// Dashboard protegido
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');


// Ruta raíz redirige al login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


Route::get('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');
Route::post('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->middleware('throttle:5,1');
Route::get('/3fa/verify', [App\Http\Controllers\Auth\ThreeFactorController::class, 'showVerifyForm'])
    ->name('3fa.verify')
    ->middleware('throttle:3,1');  // máximo 3 visitas cada 10 minutos
Route::post('/3fa/verify', [App\Http\Controllers\Auth\ThreeFactorController::class, 'verify'])->middleware('throttle:5,1');;
Route::post('/3fa/resend', [App\Http\Controllers\Auth\ThreeFactorController::class, 'resendCode'])->name('3fa.resend');
// Dashboard general
// Invitado
Route::get('/dashboard/invitado', function () {
    return view('dashboard-invitado');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.invitado');

// Usuario
Route::get('/dashboard/usuario', function () {
    return view('dashboard-usuario');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.usuario');

// Admin
Route::get('/dashboard/admin', function () {
    $loginLogs = LoginLog::orderBy('created_at', 'desc')->take(50)->get();
    $registrationLogs = RegistrationLog::orderBy('created_at', 'desc')->take(50)->get();
    return view('dashboard-admin', compact('loginLogs', 'registrationLogs'));
})->middleware(['auth', 'mfa.complete'])->name('dashboard.admin');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('throttle:5,10');