<?php

use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ServicePackageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CustomerTypeController;

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

// Ruta de bienvenida (página inicial)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas de autenticación con middleware de redirección por rol
Auth::routes(['middleware' => 'role.redirect']);

// Rutas para dashboards según roles
Route::middleware(['auth'])->group(function () {
    // Dashboard para el rol "admin"
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });

    // Dashboard para el rol "gestion_calidad"
    Route::middleware('role:gestion_calidad')->group(function () {
        Route::get('/gestion_calidad', function () {
            return view('gestion_calidad.dashboard');
        })->name('gestion_calidad.dashboard');
    });

    // Dashboard para el rol "personal_tecnico"
    Route::middleware('role:personal_tecnico')->group(function () {
        Route::get('/personal_tecnico', function () {
            return view('personal_tecnico.dashboard');
        })->name('personal_tecnico.dashboard');
    });

    // Dashboard para el rol "pasante"
    Route::middleware('role:pasante')->group(function () {
        Route::get('/pasante', function () {
            return view('pasante.dashboard');
        })->name('pasante.dashboard');
    });
});

// Rutas para Usuarios (requiere autenticación)
Route::middleware(['auth'])->group(function () {
    Route::resource('usuarios', UsuariosController::class)->names([
        'index' => 'listab',
        'create' => 'usuarios.create',
        'store' => 'usuarios.store',
        'update' => 'usuarios.update',
        'destroy' => 'usuarios.destroy',
    ]);
});

// Rutas para Clientes (Customers)
Route::middleware(['auth'])->group(function () {
    Route::resource('customers', CustomersController::class)->names([
        'index' => 'listac',
        'create' => 'customers.create',
        'store' => 'customers.store',
        'update' => 'customers.update',
        'destroy' => 'customers.destroy',
    ]);
});

// Rutas para Servicios (Services)
Route::middleware(['auth'])->group(function () {
    Route::resource('services', ServicesController::class)->names([
        'index' => 'listas',
        'create' => 'services.create',
        'store' => 'services.store',
        'update' => 'services.update',
        'destroy' => 'services.destroy',
    ]);
});


Route::prefix('cotizaciones')->middleware(['auth'])->group(function () {
    Route::get('/', [QuoteController::class, 'index'])->name('cotizacion.index');
    Route::get('/crear', [QuoteController::class, 'create'])->name('cotizacion.create');
    Route::post('/store', [QuoteController::class, 'store'])->name('cotizacion.store');
    Route::get('/lista', [QuoteController::class, 'lista'])->name('cotizacion.lista');
    Route::get('/{quote_id}', [QuoteController::class, 'show'])->name('cotizacion.show');
    Route::get('/{quote_id}/editar', [QuoteController::class, 'edit'])->name('cotizacion.edit');
    Route::put('/{quote_id}', [QuoteController::class, 'update'])->name('cotizacion.update');
    Route::delete('/{quote_id}', [QuoteController::class, 'destroy'])->name('cotizacion.destroy');
    Route::get('/{quote_id}/upload', [QuoteController::class, 'showUploadForm'])->name('cotizacion.upload'); // Añadida para mostrar formulario
    Route::post('/{quote_id}/upload', [QuoteController::class, 'upload'])->name('cotizacion.upload.store'); // Cambiado de 'quote.upload' a 'cotizacion.upload.store'
    Route::get('/crear-minima', [QuoteController::class, 'createMinima'])->name('cotizacion.create_minima');
    Route::get('/{quote_id}/comprobante', [QuoteController::class, 'comprobante'])->name('cotizacion.comprobante');
});
// Rutas para paquetes de servicios
Route::prefix('service-packages')->middleware(['auth'])->group(function () {
    Route::get('/', [ServicePackageController::class, 'index'])->name('service_packages.index');
    Route::get('/create', [ServicePackageController::class, 'create'])->name('service_packages.create');
    Route::post('/store', [ServicePackageController::class, 'store'])->name('service_packages.store');
    Route::get('/{service_packages_id}/edit', [ServicePackageController::class, 'edit'])->name('service_packages.edit');
    Route::put('/{service_packages_id}', [ServicePackageController::class, 'update'])->name('service_packages.update');
    Route::delete('/{service_packages_id}', [ServicePackageController::class, 'destroy'])->name('service_packages.destroy');
});

// Rutas para Tipos de Cliente (CustomerType)
Route::middleware(['auth'])->group(function () {
    Route::resource('customer-types', CustomerTypeController::class)->names([
        'index' => 'customer_types.index',
        'create' => 'customer_types.create',
        'store' => 'customer_types.store',
        'edit' => 'customer_types.edit',
        'update' => 'customer_types.update',
        'destroy' => 'customer_types.destroy',
    ]);
});