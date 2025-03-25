<?php

use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\QuoteController;
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

Auth::routes(['middleware' => 'role.redirect']);

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

Route::middleware(['auth'])->group(function () {
    Route::get('/formu', [CotizacionController::class, 'create'])->name('cotizacion.create');
    Route::get('/lista', [CotizacionController::class, 'index'])->name('lista');
    Route::post('/store', [CotizacionController::class, 'store'])->name('cotizacion.store');
    Route::put('/cotizacion/{id}', [CotizacionController::class, 'update'])->name('cotizacion.update');
    Route::delete('/cotizacion/{id}', [CotizacionController::class, 'destroy'])->name('cotizacion.destroy');
    Route::put('/cotizacion/{id}/payment', [CotizacionController::class, 'updatePaymentStatus'])->name('cotizacion.payment.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/listab', [UsuariosController::class, 'index'])->name('listab');
    Route::get('/formub', [UsuariosController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios/store', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
});

Route::get('/formuc', [CustomersController::class, 'create'])->name('customers.create');
Route::get('/listac', [CustomersController::class, 'index'])->name('listac');
Route::post('/store', [CustomersController::class, 'store'])->name('customers.store');
Route::put('/customers/{id}', [CustomersController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomersController::class, 'destroy'])->name('customers.destroy');

Route::get('/formus', [ServicesController::class, 'create'])->name('services.create');
Route::get('/listas', [ServicesController::class, 'index'])->name('listas');
Route::post('/services/store', [ServicesController::class, 'store'])->name('services.store');
Route::put('/services/{id}', [ServicesController::class, 'update'])->name('services.update');
Route::delete('/services/{id}', [ServicesController::class, 'destroy'])->name('services.destroy');

Route::get('/cotizacion', [QuoteController::class, 'create'])->name('cotizacion.create');
Route::post('/cotizacion/store', [QuoteController::class, 'store'])->name('cotizacion.store');
Route::get('/cotizacion/{id}/edit', [QuoteController::class, 'edit'])->name('cotizacion.edit');
Route::put('/cotizacion/{id}', [QuoteController::class, 'update'])->name('cotizacion.update');
Route::delete('/cotizacion/{id}', [QuoteController::class, 'destroy'])->name('cotizacion.destroy');
Route::get('/lista', [QuoteController::class, 'index'])->name('lista');
Route::get('/quote/{id}/upload', [QuoteController::class, 'showUploadForm'])->name('quote.upload');
Route::post('/quote/{id}/upload', [QuoteController::class, 'uploadFile'])->name('quote.upload.file');
Route::get('/quote/{id}/pdf', [QuoteController::class, 'generatePDF'])->name('quote.pdf');