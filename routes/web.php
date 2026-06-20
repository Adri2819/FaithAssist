<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Ecclesiastes\ChapelController;
use App\Http\Controllers\Ecclesiastes\ChurchController;
use App\Http\Controllers\Ecclesiastes\DeaneryController;
use App\Http\Controllers\Ecclesiastes\DioceseController;
use App\Http\Controllers\Operation\PeriodMovementController;
use App\Http\Controllers\Operation\PeriodController;
use App\Http\Controllers\Operation\LevelController;
use App\Http\Controllers\Regions\CommunityController;
use App\Http\Controllers\Regions\MunicipalityController;
use App\Http\Controllers\Regions\StateController;
use App\Http\Controllers\Security\ModuleController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappMessageController;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('home');

    Route::get('/profile', function () {
        return Inertia::render('Profile/Show');
    })->name('profile.show');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Catalogos - Regiones
    Route::resource('estados', StateController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['estados' => 'estado']);

    Route::resource('municipios', MunicipalityController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['municipios' => 'municipio']);

    Route::resource('comunidades', CommunityController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['comunidades' => 'comunidad']);

    Route::get('/comunidades/export', [CommunityController::class, 'export'])->name('comunidades.export');

    // Catalogos - Eclesiasticos
    Route::resource('diocesis', DioceseController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['diocesis' => 'diocesis']);

    Route::resource('decanatos', DeaneryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['decanatos' => 'decanato']);

    Route::resource('parroquias', ChurchController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['parroquias' => 'parroquia']);

    Route::resource('capillas', ChapelController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['capillas' => 'capilla']);

    // Operacion
    Route::resource('periodos', PeriodController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['periodos' => 'periodo']);

    Route::resource('periodo-movimientos', PeriodMovementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['periodo-movimientos' => 'periodo_movimiento']);

    Route::resource('niveles', LevelController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['niveles' => 'nivel']);

    // Seguridad
    Route::resource('modulos', ModuleController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['modulos' => 'modulo']);

    Route::resource('permisos', PermissionController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['permisos' => 'permiso']);

    Route::resource('roles', RoleController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::resource('usuarios', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['usuarios' => 'usuario']);

    if (app()->environment('local')) {
        Route::get('/test-meta-config', function () {
            return [
                'token_exists' => config('meta.whatsapp.token') ? true : false,
                'phone_number_id' => config('meta.whatsapp.phone_number_id'),
                'api_version' => config('meta.whatsapp.api_version'),
                'base_url' => config('meta.whatsapp.base_url'),
            ];
        });
    }

    Route::get('/whatsapp', [WhatsappMessageController::class, 'index'])->name('whatsapp.index');

    Route::post('/whatsapp/send', [WhatsappMessageController::class, 'send'])->name('whatsapp.send');

    Route::get('/whatsapp/history', [WhatsappMessageController::class, 'history'])->name('whatsapp.history');

    Route::get('/whatsapp/history-json', [WhatsappMessageController::class, 'historyJson'])->name('whatsapp.history-json');
});
