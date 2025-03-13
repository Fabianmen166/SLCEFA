<?php
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\CotizacionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación protegidas por el middleware centralizado
Auth::routes(['middleware' => 'role.redirect']);

// Rutas protegidas por roles
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

Route::middleware(['auth', 'role:gestion_calidad'])->group(function () {
    Route::get('/gestion_calidad', function () {
        return view('gestion_calidad.dashboard');
    })->name('gestion_calidad.dashboard');
});

Route::middleware(['auth', 'role:personal_tecnico'])->group(function () {
    Route::get('/personal_tecnico', function () {
        return view('personal_tecnico.dashboard');
    })->name('personal_tecnico.dashboard');
});

Route::middleware(['auth', 'role:pasante'])->group(function () {
    Route::get('/pasante', function () {
        return view('pasante.dashboard');
    })->name('pasante.dashboard');
});

Route::get('/formu', [CotizacionController::class, 'create'])->name('cotizacion.create');
Route::get('/lista', [CotizacionController::class, 'index'])->name('lista');
Route::post('/store',[CotizacionController::class, 'store'])->name('cotizacion.store');
Route::put('/cotizacion/{id}', [CotizacionController::class, 'update'])->name('cotizacion.update');
Route::delete('/cotizacion/{id}', [CotizacionController::class, 'destroy'])->name('cotizacion.destroy');
Route::put('/cotizacion/{id}/payment', [CotizacionController::class, 'updatePaymentStatus'])->name('cotizacion.payment.update');
Route::get('/listab', [UsuariosController::class, 'index'])->name('listab');

Route::get('/formub', [UsuariosController::class, 'create'])->name('usuarios.create');
Route::post('/usuarios/store', [UsuariosController::class, 'store'])->name('usuarios.store');
Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');



