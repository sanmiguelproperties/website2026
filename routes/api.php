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
use App\Http\Controllers\FrontendColorController;
use App\Http\Controllers\EasyBrokerSyncController;
use App\Http\Controllers\MLSSyncController;

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

// Color Theme routes protegidas con autenticación Passport (Dashboard)
Route::middleware(['auth.api', 'admin.api'])->group(function () {
    Route::apiResource('color-themes', ColorThemeController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('color-themes/{id}/activate', [ColorThemeController::class, 'activate']);
    Route::get('color-themes/active', [ColorThemeController::class, 'active']);
});

// Frontend Color Settings routes (públicas y protegidas)
// Rutas públicas: CSS y colores activos (necesarios para renderizar el frontend)
Route::prefix('frontend-colors')->group(function () {
    // CSS para cualquier vista
    Route::get('css', [FrontendColorController::class, 'css']);
    Route::get('css/{viewSlug}', [FrontendColorController::class, 'cssForView']);
    
    // Colores activos
    Route::get('active', [FrontendColorController::class, 'active']);
    Route::get('active/{viewSlug}', [FrontendColorController::class, 'activeForView']);
    
    // Colores por defecto
    Route::get('defaults', [FrontendColorController::class, 'defaults']);
    Route::get('defaults/{viewSlug}', [FrontendColorController::class, 'defaultsForView']);
    
    // Vistas disponibles (público para que el frontend sepa qué vistas existen)
    Route::get('views', [FrontendColorController::class, 'views']);
});

// Rutas protegidas para administración de colores del frontend
Route::middleware(['auth.api', 'admin.api'])->prefix('frontend-colors')->group(function () {
    // Listado y creación
    Route::get('/', [FrontendColorController::class, 'index']);
    Route::post('/', [FrontendColorController::class, 'store']);
    
    // Agrupado por vista
    Route::get('grouped', [FrontendColorController::class, 'groupedByView']);
    
    // Grupos de colores
    Route::get('groups', [FrontendColorController::class, 'groups']);
    Route::get('groups/{viewSlug}', [FrontendColorController::class, 'groupsForView']);
    
    // Configuraciones por vista
    Route::get('view/{viewSlug}', [FrontendColorController::class, 'viewConfigs']);
    
    // CRUD individual
    Route::get('{id}', [FrontendColorController::class, 'show'])->where('id', '[0-9]+');
    Route::put('{id}', [FrontendColorController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('{id}', [FrontendColorController::class, 'destroy'])->where('id', '[0-9]+');
    
    // Acciones
    Route::post('{id}/activate', [FrontendColorController::class, 'activate'])->where('id', '[0-9]+');
    Route::post('{id}/reset-defaults', [FrontendColorController::class, 'resetDefaults'])->where('id', '[0-9]+');
    Route::post('{id}/duplicate', [FrontendColorController::class, 'duplicate'])->where('id', '[0-9]+');
    Route::get('{id}/export', [FrontendColorController::class, 'export'])->where('id', '[0-9]+');
    
    // Importar
    Route::post('import', [FrontendColorController::class, 'import']);
});

// EasyBroker Sync routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api'])->prefix('easybroker')->group(function () {
    // Estado de configuración
    Route::get('status', [EasyBrokerSyncController::class, 'status']);
    
    // Gestión de configuración
    Route::get('config', [EasyBrokerSyncController::class, 'getConfig']);
    Route::put('config', [EasyBrokerSyncController::class, 'updateConfig']);
    Route::delete('config/api-key', [EasyBrokerSyncController::class, 'deleteApiKey']);
    
    // Probar conexión
    Route::get('test-connection', [EasyBrokerSyncController::class, 'testConnection']);
    
    // Ejecutar sincronización
    Route::post('sync', [EasyBrokerSyncController::class, 'sync']);
});

// MLS AMPI San Miguel de Allende Sync routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api'])->prefix('mls')->group(function () {
    // Estado de configuración
    Route::get('status', [MLSSyncController::class, 'status']);
    
    // Gestión de configuración
    Route::get('config', [MLSSyncController::class, 'getConfig']);
    Route::put('config', [MLSSyncController::class, 'updateConfig']);
    Route::delete('config/api-key', [MLSSyncController::class, 'deleteApiKey']);
    
    // Probar conexión
    Route::get('test-connection', [MLSSyncController::class, 'testConnection']);
    
    // Ejecutar sincronización (solo datos, sin imágenes)
    Route::post('sync', [MLSSyncController::class, 'sync']);

    // Ejecutar sincronización completa incluyendo imágenes
    Route::post('sync-with-images', [MLSSyncController::class, 'syncWithImages']);

    // Sincronizar imágenes de propiedades existentes
    Route::post('sync-images', [MLSSyncController::class, 'syncImages']);
    
    // Sincronización progresiva de imágenes (procesa en lotes)
    Route::post('sync-images/progressive', [MLSSyncController::class, 'syncImagesProgressive']);
    
    // Obtener progreso de sincronización de imágenes
    Route::get('sync-images/progress', [MLSSyncController::class, 'getImagesSyncProgress']);
    
    // Catálogos del MLS
    Route::get('features', [MLSSyncController::class, 'features']);
    Route::get('neighborhoods', [MLSSyncController::class, 'neighborhoods']);
    Route::get('agents', [MLSSyncController::class, 'agents']);
    Route::get('allowed-values', [MLSSyncController::class, 'allowedValues']);
    
    // Consultar propiedad específica del MLS
    Route::get('property/{mlsId}', [MLSSyncController::class, 'property']);

    // Eliminar todas las propiedades del MLS
    Route::delete('properties', [MLSSyncController::class, 'deleteAllMLSProperties']);
    
    // Nuevas rutas para manejo robusto de errores
    // Obtener detalles de errores de la última sincronización
    Route::get('error-details', [MLSSyncController::class, 'getErrorDetails']);
    
    // Obtener estado del circuit breaker
    Route::get('circuit-breaker', [MLSSyncController::class, 'getCircuitBreakerStatus']);
    
    // Reiniciar circuit breaker manualmente
    Route::post('circuit-breaker/reset', [MLSSyncController::class, 'resetCircuitBreaker']);
    
    // Obtener checkpoint de sincronización
    Route::get('checkpoint', [MLSSyncController::class, 'getCheckpoint']);
    
    // Limpiar checkpoint de sincronización
    Route::delete('checkpoint', [MLSSyncController::class, 'clearCheckpoint']);
    
    // Retomar sincronización desde checkpoint
    Route::post('sync/resume', [MLSSyncController::class, 'syncResume']);
});
