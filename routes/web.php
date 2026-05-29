<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Ecclesiastes\DeaneryController;
use App\Http\Controllers\Ecclesiastes\DioceseController;
use App\Http\Controllers\Regions\MunicipalityController;
use App\Http\Controllers\Regions\StateController;
use Illuminate\Support\Facades\Route;
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

    // Catalogos - Eclesiasticos
    Route::resource('diocesis', DioceseController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['diocesis' => 'diocesis']);

    Route::resource('decanatos', DeaneryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['decanatos' => 'decanato']);
});