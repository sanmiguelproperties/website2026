<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| View Routes
|--------------------------------------------------------------------------
|
| Aquí se definen todas las rutas relacionadas con las vistas web.
| Estas rutas están separadas de las rutas API para mantener el orden.
|
*/

// Ruta principal
Route::get('/', function () {
    return view('welcome');
})->middleware('guest');

// Rutas de autenticación (vistas)
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login')->middleware('guest');

// Rutas protegidas por autenticación (vistas)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/funnel', function () {
            return view('funnel');
        })->name('funnel');

        Route::get('/users', function () {
            return view('users.manage');
        })->name('users');

        Route::get('/rbac', function () {
            return view('rbac.manage');
        })->name('rbac');

        Route::get('/currencies', function () {
            return view('currencies.manage');
        })->name('currencies');

        Route::get('/properties', function () {
            return view('properties.manage');
        })->name('properties');

        Route::get('/color-themes', function () {
            return view('color-themes.manage');
        })->name('color-themes');
    });
});
