<?php

use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ServicePackageController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\PhAnalysisController;
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

// Ruta de bienvenida (página inicial)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas de autenticación con middleware de redirección por rol
Auth::routes(['middleware' => 'role.redirect']);

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboards según roles
    Route::middleware('role:admin')->get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::middleware('role:gestion_calidad')->get('/gestion_calidad', function () {
        return view('gestion_calidad.dashboard');
    })->name('gestion_calidad.dashboard');

    Route::middleware('role:personal_tecnico')->get('/personal_tecnico', function () {
        return view('personal_tecnico.dashboard');
    })->name('personal_tecnico.dashboard');

    Route::middleware('role:pasante')->get('/pasante', function () {
        return view('pasante.dashboard');
    })->name('pasante.dashboard');

    // Rutas para Usuarios
    Route::resource('usuarios', UsuariosController::class)->names([
        'index' => 'listab',
        'create' => 'usuarios.create',
        'store' => 'usuarios.store',
        'update' => 'usuarios.update',
        'destroy' => 'usuarios.destroy',
    ]);

    // Rutas para Clientes (Customers)
    Route::resource('customers', CustomersController::class)->names([
        'index' => 'listac',
        'create' => 'customers.create',
        'store' => 'customers.store',
        'update' => 'customers.update',
        'destroy' => 'customers.destroy',
    ]);

    // Rutas para Servicios (Services)
    Route::resource('services', ServicesController::class)->names([
        'index' => 'listas',
        'create' => 'services.create',
        'store' => 'services.store',
        'update' => 'services.update',
        'destroy' => 'services.destroy',
    ]);

    // Rutas para Paquetes de Servicios (Service Packages)
    Route::prefix('service-packages')->group(function () {
        Route::get('/', [ServicePackageController::class, 'index'])->name('service_packages.index');
        Route::get('/create', [ServicePackageController::class, 'create'])->name('service_packages.create');
        Route::post('/store', [ServicePackageController::class, 'store'])->name('service_packages.store');
        Route::get('/{service_packages_id}/edit', [ServicePackageController::class, 'edit'])->name('service_packages.edit');
        Route::put('/{service_packages_id}', [ServicePackageController::class, 'update'])->name('service_packages.update');
        Route::delete('/{service_packages_id}', [ServicePackageController::class, 'destroy'])->name('service_packages.destroy');
    });

    // Rutas para Tipos de Cliente (Customer Types)
    Route::resource('customer-types', CustomerTypeController::class)->names([
        'index' => 'customer_types.index',
        'create' => 'customer_types.create',
        'store' => 'customer_types.store',
        'edit' => 'customer_types.edit',
        'update' => 'customer_types.update',
        'destroy' => 'customer_types.destroy',
    ]);

    // Ruta para Procesos Abiertos
    Route::get('/cotizaciones/process', [ProcessController::class, 'index'])->name('processes.index');

    // Rutas para Cotizaciones y Procesos
    Route::prefix('cotizaciones')->group(function () {
        // Cotizaciones
        Route::get('cotizaciones', [QuoteController::class, 'index'])->name('cotizacion.index');
    Route::get('cotizaciones/lista', [QuoteController::class, 'lista'])->name('cotizacion.lista');
    Route::get('cotizaciones/create', [QuoteController::class, 'create'])->name('cotizacion.create');
    Route::post('cotizaciones', [QuoteController::class, 'store'])->name('cotizacion.store');
    Route::get('cotizaciones/cotizaciones/{quote_id}', [QuoteController::class, 'show'])->name('cotizacion.show');
    Route::get('cotizaciones/{id}/editar', [QuoteController::class, 'edit'])->name('cotizacion.edit');
    Route::put('cotizaciones/{id}', [QuoteController::class, 'update'])->name('cotizacion.update');
    Route::delete('cotizaciones/{id}', [QuoteController::class, 'destroy'])->name('cotizacion.destroy');
    Route::post('cotizaciones/{id}/upload', [QuoteController::class, 'upload'])->name('cotizacion.upload');
    Route::get('cotizaciones/{id}/comprobante', [QuoteController::class, 'comprobante'])->name('cotizacion.comprobante');
    Route::get('cotizaciones/{id}/upload-form', [QuoteController::class, 'showUploadForm'])->name('cotizacion.show_upload_form');
        // Procesos
        Route::post('/{quote}/start-process', [ProcessController::class, 'start'])->name('process.start');
        Route::get('/process/{process}', [ProcessController::class, 'show'])->name('processes.show');
        // routes/web.php
Route::delete('/cotizaciones/process/{process_id}', [ProcessController::class, 'destroy'])->name('cotizacion.process.destroy');
Route::post('/cotizaciones/process/{process_id}/archive', [ProcessController::class, 'archive'])->name('cotizacion.process.archive');
    });
Route::post('/cotizaciones/{quote_id}/process/start', [ProcessController::class, 'start'])->name('cotizacion.process.start');
    Route::middleware('role:personal_tecnico')->group(function () {
        Route::get('/ph-analyses', [PhAnalysisController::class, 'index'])->name('ph_analysis.index'); // Add this route
        Route::get('/processes/technical', [ProcessController::class, 'technicalIndex'])->name('process.technical_index');

        // pH Analysis Routes
        Route::get('/ph-analyses/{processId}/{serviceId}', [PhAnalysisController::class, 'phAnalysis'])->name('ph_analysis.ph_analysis');
        Route::post('/ph-analyses/{processId}/{serviceId}', [PhAnalysisController::class, 'storePhAnalysis'])->name('ph_analysis.store_ph_analysis');
        Route::get('/ph-analyses/report/{analysisId}', [PhAnalysisController::class, 'downloadPhReport'])->name('ph_analysis.download_report');
        Route::post('/ph-analyses/batch', [PhAnalysisController::class, 'batchPhAnalysis'])->name('ph_analysis.batch_ph_analysis');
    });

    // Rutas para Revisión (Admin y Gestión de Calidad)
    Route::middleware('role:admin,gestion_calidad')->group(function () {
        Route::get('/ph-analyses/review', [PhAnalysisController::class, 'indexForReview'])->name('ph_analysis.review_index');
        Route::get('/ph-analyses/review/{analysis_id}', [PhAnalysisController::class, 'reviewAnalysis'])->name('ph_analysis.review_analysis');
        Route::post('/ph-analyses/review/{phAnalysisId}', [PhAnalysisController::class, 'storeReview'])->name('ph_analysis.store_review');
        Route::get('/ph-analyses/edit/{phAnalysisId}', [PhAnalysisController::class, 'editAnalysis'])->name('ph_analysis.edit_analysis');
        Route::patch('/ph-analyses/update/{phAnalysisId}', [PhAnalysisController::class, 'updateAnalysis'])->name('ph_analysis.update_analysis');

        Route::get('/cotizaciones/process/{process_id}/results-pdf', [ProcessController::class, 'generateResultsPDF'])->name('cotizacion.process.results_pdf');
    });

    // Rutas para Procesos (Análisis Técnico y Revisión)
    Route::middleware('role:personal_tecnico')->group(function () {
        Route::get('/processes/{process_id}/ph-analysis/{service_id}', [ProcessController::class, 'phAnalysis'])->name('process.ph_analysis');
Route::post('/processes/{process_id}/ph-analysis/{service_id}', [ProcessController::class, 'storePhAnalysis'])->name('process.store_ph_analysis');
Route::get('/processes/ph-analysis/{analysisId}/download', [ProcessController::class, 'downloadPhReport'])->name('process.download_ph_report');
        Route::get('/processes/technical', [ProcessController::class, 'technicalIndex'])->name('process.technical_index');
        Route::get('/process/technical/{process_id}', [ProcessController::class, 'technicalAnalysis'])->name('process.technical_analysis');
        Route::post('/process/{process_id}/service/{service_id}/analysis', [ProcessController::class, 'storeAnalysis'])->name('process.store_analysis');
        Route::get('/process/edit-analysis/{analysis_id}', [ProcessController::class, 'editAnalysis'])->name('process.edit_analysis');
        Route::post('/process/update-analysis/{analysis_id}', [ProcessController::class, 'updateAnalysis'])->name('process.update_analysis');
        Route::get('/ph-analyses', [PhAnalysisController::class, 'index'])->name('ph_analysis.index');
        Route::get('/ph-analyses/process-all', [PhAnalysisController::class, 'processAll'])->name('ph_analysis.process_all'); 
 Route::post('/ph-analyses/store', [PhAnalysisController::class, 'storePhAnalysis'])->name('ph_analysis.store');   
        Route::post('/ph-analyses/store', [PhAnalysisController::class, 'storePhAnalysis'])->name('ph_analysis.store');
    });

    Route::middleware('role:admin')->group(function () {
     Route::get('/processes/review', [ProcessController::class, 'indexForReview'])->name('process.review_index');
Route::post('/process/{analysis_id}/review', [ProcessController::class, 'storeReview'])->name('process.store_review');
Route::get('/processes/completed', [ProcessController::class, 'completedProcesses'])->name('process.completed');
Route::get('/process/{processId}/generate-report', [ProcessController::class, 'generateReport'])->name('process.generate_report');
Route::get('/process/{processId}/preview-report', [ProcessController::class, 'previewReport'])->name('process.preview_report');
    });
});


Route::prefix('conductivity')->group(function () {
    Route::get('/index', [App\Http\Controllers\ConductivityAnalysisController::class, 'index'])->name('conductivity.index');
    Route::get('/create', [App\Http\Controllers\ConductivityAnalysisController::class, 'create'])->name('conductivity.create');
    Route::post('/store', [App\Http\Controllers\ConductivityAnalysisController::class, 'store'])->name('conductivity.store');
});