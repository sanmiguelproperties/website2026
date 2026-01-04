<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MediaAssetController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ColorThemeController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LocationCatalogController;
use App\Http\Controllers\ContactRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de autenticación API
Route::post('/login', [AuthController::class, 'apiLogin']);

// Media Manager routes
Route::apiResource('media', MediaAssetController::class);

// Rutas públicas para el portal inmobiliario (sin autenticación)
Route::prefix('public')->group(function () {
    Route::get('properties', [PropertyController::class, 'indexPublic']);
    Route::get('properties/{property}', [PropertyController::class, 'showPublic']);
});

// User Management routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api'])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('users/{userId}/roles', [UserController::class, 'getUserRoles']);
    Route::get('users/{userId}/permissions', [UserController::class, 'getUserPermissions']);
    Route::post('users/{userId}/roles/assign', [UserController::class, 'assignRoles']);
    Route::post('users/{userId}/roles/revoke', [UserController::class, 'revokeRoles']);
});

// Currency routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api'])->group(function () {
    Route::apiResource('currencies', CurrencyController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});

// EasyBroker / Inventario routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api'])->group(function () {
    Route::apiResource('agencies', AgencyController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('properties', PropertyController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('features', FeatureController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('tags', TagController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('locations-catalog', LocationCatalogController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('contact-requests', ContactRequestController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});

// RBAC routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api'])->prefix('rbac')->group(function () {
    Route::apiResource('roles', RoleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('roles/{roleId}/permissions', [RolePermissionController::class, 'index']);
    Route::post('roles/{roleId}/permissions/attach', [RolePermissionController::class, 'attach']);
    Route::post('roles/{roleId}/permissions/sync', [RolePermissionController::class, 'sync']);
    Route::post('roles/{roleId}/permissions/detach', [RolePermissionController::class, 'detach']);
});

// Color Theme routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api'])->group(function () {
    Route::apiResource('color-themes', ColorThemeController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('color-themes/{id}/activate', [ColorThemeController::class, 'activate']);
    Route::get('color-themes/active', [ColorThemeController::class, 'active']);
});
