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

/**
 * Muestra el formulario de inicio de sesión.
 *
 * @route  GET /login
 * @name   login
 * @uses   LoginController::showLoginForm()
 */
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

/**
 * Procesa las credenciales del formulario de inicio de sesión.
 *
 * Aplica rate limiting de 5 intentos por minuto por IP para mitigar
 * ataques de fuerza bruta.
 *
 * @route      POST /login
 * @middleware throttle:5,1
 * @uses       LoginController::login()
 */
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1');

/**
 * Cierra la sesión del usuario autenticado.
 *
 * @route  POST /cerrar-sesion-ahora
 * @name   logout.ahora
 * @uses   LoginController::logout()
 */
Route::post('/cerrar-sesion-ahora', [LoginController::class, 'logout'])->name('logout.ahora');

/**
 * Muestra el dashboard principal de la aplicación.
 *
 * Acceso restringido a usuarios autenticados.
 *
 * @route      GET /dashboard
 * @name       dashboard
 * @middleware auth
 * @return     \Illuminate\View\View  Vista 'dashboard'
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

/**
 * Redirige la ruta raíz al formulario de inicio de sesión.
 *
 * @route  GET /
 * @name   home
 * @return \Illuminate\Http\RedirectResponse  Redirección a la ruta 'login'
 */
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

/**
 * Muestra el formulario de verificación del segundo factor de autenticación (2FA).
 *
 * @route  GET /2fa/verify
 * @name   2fa.verify
 * @uses   TwoFactorController::showVerifyForm()
 */
Route::get('/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');

/**
 * Procesa y valida el código del segundo factor de autenticación (2FA).
 *
 * Aplica rate limiting de 5 intentos por minuto para evitar enumeración de códigos.
 *
 * @route      POST /2fa/verify
 * @middleware throttle:5,1
 * @uses       TwoFactorController::verify()
 */
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->middleware('throttle:5,1');

/**
 * Muestra el formulario de verificación del tercer factor de autenticación (3FA).
 *
 * Aplica rate limiting de 3 visitas por minuto para limitar la exposición
 * del formulario a bots o automatizaciones.
 *
 * @route      GET /3fa/verify
 * @name       3fa.verify
 * @middleware throttle:3,1
 * @uses       ThreeFactorController::showVerifyForm()
 */
Route::get('/3fa/verify', [ThreeFactorController::class, 'showVerifyForm'])
    ->name('3fa.verify')
    ->middleware('throttle:3,1');

/**
 * Procesa y valida el código del tercer factor de autenticación (3FA).
 *
 * Aplica rate limiting de 5 intentos por minuto para proteger contra
 * ataques de fuerza bruta sobre el código 3FA.
 *
 * @route      POST /3fa/verify
 * @middleware throttle:5,1
 * @uses       ThreeFactorController::verify()
 */
Route::post('/3fa/verify', [ThreeFactorController::class, 'verify'])->middleware('throttle:5,1');

/**
 * Reenvía el código del tercer factor de autenticación (3FA) al usuario.
 *
 * @route  POST /3fa/resend
 * @name   3fa.resend
 * @uses   ThreeFactorController::resendCode()
 */
Route::post('/3fa/resend', [ThreeFactorController::class, 'resendCode'])->name('3fa.resend');

/**
 * Muestra el dashboard para usuarios con rol de invitado.
 *
 * Requiere autenticación y que el flujo MFA esté completado.
 *
 * @route      GET /dashboard/invitado
 * @name       dashboard.invitado
 * @middleware auth, mfa.complete
 * @return     \Illuminate\View\View  Vista 'dashboard-invitado'
 */
Route::get('/dashboard/invitado', function () {
    return view('dashboard-invitado');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.invitado');

/**
 * Muestra el dashboard para usuarios con rol estándar.
 *
 * Requiere autenticación y que el flujo MFA esté completado.
 *
 * @route      GET /dashboard/usuario
 * @name       dashboard.usuario
 * @middleware auth, mfa.complete
 * @return     \Illuminate\View\View  Vista 'dashboard-usuario'
 */
Route::get('/dashboard/usuario', function () {
    return view('dashboard-usuario');
})->middleware(['auth', 'mfa.complete'])->name('dashboard.usuario');

/**
 * Muestra el dashboard para administradores con logs de actividad reciente.
 *
 * Carga los últimos 50 registros de inicio de sesión y los últimos 50 registros
 * de registro de nuevos usuarios, ordenados por fecha descendente.
 *
 * Requiere autenticación y que el flujo MFA esté completado.
 *
 * @route      GET /dashboard/admin
 * @name       dashboard.admin
 * @middleware auth, mfa.complete
 * @return     \Illuminate\View\View  Vista 'dashboard-admin' con variables:
 *                                    - \Illuminate\Database\Eloquent\Collection $loginLogs
 *                                    - \Illuminate\Database\Eloquent\Collection $registrationLogs
 */
Route::get('/dashboard/admin', function () {
    $loginLogs = LoginLog::orderBy('created_at', 'desc')->take(50)->get();
    $registrationLogs = RegistrationLog::orderBy('created_at', 'desc')->take(50)->get();
    return view('dashboard-admin', compact('loginLogs', 'registrationLogs'));
})->middleware(['auth', 'mfa.complete'])->name('dashboard.admin');

/**
 * Muestra el formulario de registro de nuevos usuarios.
 *
 * @route  GET /register
 * @name   register
 * @uses   RegisterController::showRegisterForm()
 */
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegisterForm'])->name('register');

/**
 * Procesa el registro de un nuevo usuario en el sistema.
 *
 * Aplica rate limiting de 5 intentos cada 10 minutos por IP para prevenir
 * registro masivo automatizado.
 *
 * @route      POST /register
 * @middleware throttle:5,10
 * @uses       RegisterController::register()
 */
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->middleware('throttle:5,10');