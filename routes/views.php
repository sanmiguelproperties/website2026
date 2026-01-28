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

// Ruta principal - Página de inicio pública
Route::get('/', function () {
    return view('home');
})->name('home');

// Listado público de propiedades (paginación + filtros, consumiendo API pública)
Route::get('/propiedades', function () {
    return view('public.properties-index');
})->name('public.properties.index');

// Vista pública (de prueba) para detalle de propiedad
// Nota: por ahora NO hacemos binding con el modelo para permitir probar con cualquier ID.
Route::get('/propiedades/{propertyId}', function (string $propertyId) {
    return view('public.property-detail', [
        'propertyId' => (int) $propertyId,
    ]);
})
    ->where('propertyId', '[0-9]+')
    ->name('public.properties.show');

// Página de contacto pública
Route::get('/contacto', function () {
    return view('public.contact');
})->name('public.contact');

// Página pública: Nosotros
Route::get('/nosotros', function () {
    return view('public.about');
})->name('about');

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

        Route::get('/frontend-colors', function () {
            return view('frontend-colors.manage');
        })->name('frontend-colors');

        Route::get('/easybroker', function () {
            return view('easybroker.sync');
        })->name('easybroker');

        Route::get('/mls', function () {
            return view('mls.sync');
        })->name('mls');
    });
});
