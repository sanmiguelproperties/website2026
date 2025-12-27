<?php

use Illuminate\Support\Facades\Route;

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

// Incluir rutas de vistas
require __DIR__.'/views.php';

// Ruta de ejemplo para Media Manager
Route::get('/media-example', function () {
    return view('media-example');
})->name('media.example')->middleware('guest');

// Rutas de autenticación (solo las que no son vistas)
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
