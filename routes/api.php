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
use App\Http\Controllers\ZonePageController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\PropertyContactRequestController;
use App\Http\Controllers\ContactNoteController;
use App\Http\Controllers\FrontendColorController;
use App\Http\Controllers\EasyBrokerSyncController;
use App\Http\Controllers\EasyBrokerMlsExportController;
use App\Http\Controllers\MLSSyncController;
use App\Http\Controllers\MLSAgentController;
use App\Http\Controllers\MLSOfficeController;
use App\Http\Controllers\CorporateEmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicLeadController;
use App\Http\Controllers\TutorialVideoController;
use App\Http\Controllers\ManualArticleController;
use App\Http\Controllers\ManualSectionController;

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

Route::middleware(['auth.api', 'admin.api:notifications.view'])
    ->get('notifications/admin', [NotificationController::class, 'adminIndex']);

Route::middleware(['auth.api', 'admin.api:notifications.view'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/{notification}', [NotificationController::class, 'markAsRead']);
});

Route::middleware(['auth.api', 'admin.api:tutorials.view|tutorials.manage'])->prefix('tutorial-videos')->group(function () {
    Route::get('/', [TutorialVideoController::class, 'index']);
    Route::get('{tutorialVideo}', [TutorialVideoController::class, 'show'])->where('tutorialVideo', '[0-9]+');
    Route::post('/', [TutorialVideoController::class, 'store'])->middleware('admin.api:tutorials.manage');
    Route::put('{tutorialVideo}', [TutorialVideoController::class, 'update'])->where('tutorialVideo', '[0-9]+')->middleware('admin.api:tutorials.manage');
    Route::patch('{tutorialVideo}', [TutorialVideoController::class, 'update'])->where('tutorialVideo', '[0-9]+')->middleware('admin.api:tutorials.manage');
    Route::delete('{tutorialVideo}', [TutorialVideoController::class, 'destroy'])->where('tutorialVideo', '[0-9]+')->middleware('admin.api:tutorials.manage');
});

Route::middleware(['auth.api', 'admin.api:manual.view|manual.manage'])->prefix('manual')->group(function () {
    Route::get('sections', [ManualSectionController::class, 'index']);
    Route::get('videos', [ManualArticleController::class, 'videos'])->middleware('admin.api:manual.manage');
    Route::get('articles', [ManualArticleController::class, 'index']);
    Route::get('articles/{manualArticle}', [ManualArticleController::class, 'show'])->where('manualArticle', '[0-9]+');

    Route::post('sections', [ManualSectionController::class, 'store'])->middleware('admin.api:manual.manage');
    Route::put('sections/{manualSection}', [ManualSectionController::class, 'update'])->where('manualSection', '[0-9]+')->middleware('admin.api:manual.manage');
    Route::patch('sections/{manualSection}', [ManualSectionController::class, 'update'])->where('manualSection', '[0-9]+')->middleware('admin.api:manual.manage');
    Route::delete('sections/{manualSection}', [ManualSectionController::class, 'destroy'])->where('manualSection', '[0-9]+')->middleware('admin.api:manual.manage');

    Route::post('articles', [ManualArticleController::class, 'store'])->middleware('admin.api:manual.manage');
    Route::put('articles/{manualArticle}', [ManualArticleController::class, 'update'])->where('manualArticle', '[0-9]+')->middleware('admin.api:manual.manage');
    Route::patch('articles/{manualArticle}', [ManualArticleController::class, 'update'])->where('manualArticle', '[0-9]+')->middleware('admin.api:manual.manage');
    Route::delete('articles/{manualArticle}', [ManualArticleController::class, 'destroy'])->where('manualArticle', '[0-9]+')->middleware('admin.api:manual.manage');
});

// Media Manager routes
Route::middleware(['auth.api', 'admin.api:documents.view'])->group(function () {
    Route::apiResource('media', MediaAssetController::class);
});

// Rutas públicas para el portal inmobiliario (sin autenticación)
Route::prefix('public')->group(function () {
    // Oficinas / Agencias MLS (público)
    Route::get('mls-offices', [MLSOfficeController::class, 'indexPublic']);
    Route::get('mls-offices/{mlsOffice}', [MLSOfficeController::class, 'showPublic']);
    Route::get('mls-offices/{mlsOffice}/agents', [MLSOfficeController::class, 'agentsPublic']);

    // Agentes MLS (público)
    Route::get('mls-agents', [MLSAgentController::class, 'indexPublic']);
    // Los perfiles sincronizados usan el ID MLS; los manuales usan local-{id}.
    Route::get('mls-agents/{mlsAgentId}', [MLSAgentController::class, 'showPublicByMlsId'])
        ->where('mlsAgentId', '(?:[0-9]+|local-[0-9]+)');

    Route::get('properties/filter-options', [PropertyController::class, 'filterOptions']);
    Route::get('properties', [PropertyController::class, 'indexPublic']);
    Route::get('properties/{property}', [PropertyController::class, 'showPublic']);
    Route::post('contact-requests', [PublicLeadController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::post('property-contact-requests', [PropertyContactRequestController::class, 'store'])
        ->middleware('throttle:10,1');

});

// User Management routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api:users.view'])->group(function () {
    Route::get('users/mls-agent-options', [UserController::class, 'mlsAgentOptions']);
    Route::put('users/{user}/mls-agent', [UserController::class, 'updateMlsAgent'])->where('user', '[0-9]+');
    Route::post('users/{user}/mls-agent', [UserController::class, 'createMlsAgent'])->where('user', '[0-9]+');
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('users/{userId}/roles', [UserController::class, 'getUserRoles']);
    Route::get('users/{userId}/permissions', [UserController::class, 'getUserPermissions']);
    Route::post('users/{userId}/roles/assign', [UserController::class, 'assignRoles']);
    Route::post('users/{userId}/roles/revoke', [UserController::class, 'revokeRoles']);
});

// Currency routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api:settings.manage'])->group(function () {
    Route::apiResource('currencies', CurrencyController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});

// EasyBroker / Inventario routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api:catalogs.manage'])->group(function () {
    Route::get('agencies', [AgencyController::class, 'index'])->name('agencies.index');
    Route::apiResource('features', FeatureController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('tags', TagController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('locations-catalog', LocationCatalogController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
});

Route::middleware(['auth.api', 'admin.api:properties.view'])->group(function () {
    Route::post('properties/{property}/restore', [PropertyController::class, 'restore'])->where('property', '[0-9]+');
    Route::apiResource('properties', PropertyController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy'])
        ->withTrashed(['show', 'update', 'destroy']);
});

Route::middleware(['auth.api', 'admin.api:leads.view|leads.create'])->group(function () {
    Route::post('contact-requests/{contactRequest}/restore', [ContactRequestController::class, 'restore'])->where('contactRequest', '[0-9]+');
    Route::apiResource('contact-requests', ContactRequestController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy'])
        ->withTrashed(['show', 'update', 'destroy']);
    Route::get('contact-requests/{contactRequest}/notes', [ContactNoteController::class, 'index'])->where('contactRequest', '[0-9]+');
    Route::post('contact-requests/{contactRequest}/notes', [ContactNoteController::class, 'store'])->where('contactRequest', '[0-9]+');
    Route::put('contact-notes/{contactNote}', [ContactNoteController::class, 'update'])->where('contactNote', '[0-9]+');
    Route::patch('contact-notes/{contactNote}', [ContactNoteController::class, 'update'])->where('contactNote', '[0-9]+');
    Route::delete('contact-notes/{contactNote}', [ContactNoteController::class, 'destroy'])->where('contactNote', '[0-9]+');
});

Route::middleware(['auth.api', 'admin.api:settings.manage'])->group(function () {
    Route::get('zone-pages', [ZonePageController::class, 'index']);
    Route::post('zone-pages/sync', [ZonePageController::class, 'sync']);
    Route::get('zone-pages/{zonePage}', [ZonePageController::class, 'show'])->where('zonePage', '[0-9]+');
    Route::put('zone-pages/{zonePage}', [ZonePageController::class, 'update'])->where('zonePage', '[0-9]+');
});

// RBAC routes protegidas con autenticación Passport
Route::middleware(['auth.api', 'admin.api:rbac.manage'])->prefix('rbac')->group(function () {
    Route::apiResource('roles', RoleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::get('roles/{roleId}/permissions', [RolePermissionController::class, 'index']);
    Route::post('roles/{roleId}/permissions/attach', [RolePermissionController::class, 'attach']);
    Route::post('roles/{roleId}/permissions/sync', [RolePermissionController::class, 'sync']);
    Route::post('roles/{roleId}/permissions/detach', [RolePermissionController::class, 'detach']);
});

// Color Theme routes protegidas con autenticación Passport (Dashboard)
Route::middleware(['auth.api', 'admin.api:settings.manage'])->group(function () {
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
Route::middleware(['auth.api', 'admin.api:settings.manage'])->prefix('frontend-colors')->group(function () {
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
Route::middleware(['auth.api', 'admin.api:integrations.view|integrations.logs.view|integrations.sync|integrations.config.edit'])->prefix('easybroker')->group(function () {
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

    // Exportación de propiedades MLS locales hacia EasyBroker
    Route::prefix('mls-export')->group(function () {
        Route::get('offices', [EasyBrokerMlsExportController::class, 'offices']);
        Route::get('property-types', [EasyBrokerMlsExportController::class, 'propertyTypes']);
        Route::get('properties', [EasyBrokerMlsExportController::class, 'properties']);
        Route::post('send', [EasyBrokerMlsExportController::class, 'send']);
    });
});

// MLS AMPI San Miguel de Allende Sync routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api:integrations.view|integrations.logs.view|integrations.sync|integrations.config.edit'])->prefix('mls')->group(function () {
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

    // Sincronización progresiva de propiedades (para servidores con límites de tiempo)
    Route::post('sync/progressive', [MLSSyncController::class, 'syncProgressive']);

    // Obtener progreso de sincronización de propiedades
    Route::get('sync/properties/progress', [MLSSyncController::class, 'getPropertiesSyncProgress']);

    // Forzar liberación del lock (para desbloqueo de emergencia)
    Route::post('sync/unlock', [MLSSyncController::class, 'forceUnlock']);
});

// MLS Agents routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api:catalogs.manage'])->group(function () {
    Route::get('mls-agents/form-options', [MLSAgentController::class, 'formOptions']);

    // CRUD de agentes MLS
    Route::apiResource('mls-agents', MLSAgentController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Sincronizar agentes desde el MLS API
    Route::post('mls-agents/sync', [MLSAgentController::class, 'syncAgents']);

    // Re-sincronizar relaciones agente-propiedad (para propiedades existentes)
    Route::post('mls-agents/sync-property-agents', [MLSAgentController::class, 'syncPropertyAgentsRelations']);

    // Asociar/desasociar propiedades a un agente
    Route::post('mls-agents/{mlsAgent}/properties', [MLSAgentController::class, 'attachProperties']);
    Route::delete('mls-agents/{mlsAgent}/properties', [MLSAgentController::class, 'detachProperties']);

    // Agentes MLS de una propiedad
    Route::get('properties/{property}/mls-agents', [MLSAgentController::class, 'propertyAgents']);
    Route::post('properties/{property}/mls-agents', [MLSAgentController::class, 'syncPropertyAgents']);
});

// MLS Offices routes (protegidas con autenticación Passport)
Route::middleware(['auth.api', 'admin.api:catalogs.manage'])->group(function () {
    // CRUD de offices MLS (tabla mls_offices)
    Route::apiResource('mls-offices', MLSOfficeController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

    // Campo manual (no editable por sync): marcar si la agencia MLS está a nuestro cargo.
    Route::patch('mls-offices/{mlsOffice}/managed-by-us', [MLSOfficeController::class, 'updateManagedByUs']);
    Route::patch('mls-offices/{mlsOffice}/primary', [MLSOfficeController::class, 'setPrimary']);

    // Agentes y propiedades de una office
    Route::get('mls-offices/{mls_office}/agents', [MLSOfficeController::class, 'agents']);
    Route::get('mls-offices/{mls_office}/properties', [MLSOfficeController::class, 'officeProperties']);

    // Sincronizar offices desde el MLS API (progresivo)
    Route::post('mls-offices/sync', [MLSOfficeController::class, 'syncOffices']);
});

// Corporate Email routes propias del usuario autenticado
Route::middleware(['auth.api', 'admin.api:corporate-email.view|corporate-email.send|corporate-email.accounts.manage'])->prefix('corporate-email')->group(function () {
    // Bandeja propia del usuario autenticado
    Route::get('my/accounts', [CorporateEmailController::class, 'myAccounts']);
    Route::post('my/accounts/{account}/sync', [CorporateEmailController::class, 'syncMyAccount']);
    Route::get('my/inbox', [CorporateEmailController::class, 'myInbox']);
    Route::get('my/inbox/{message}', [CorporateEmailController::class, 'showMyInboxMessage']);
    Route::post('my/inbox/{message}/mark-read', [CorporateEmailController::class, 'markMyInboxMessageAsRead']);
    Route::get('my/outbox', [CorporateEmailController::class, 'myOutbox']);
    Route::get('my/outbox/{message}', [CorporateEmailController::class, 'showMyOutboxMessage']);
    Route::post('my/send', [CorporateEmailController::class, 'sendMyMessage']);
});

// Corporate Email routes administrativas
Route::middleware(['auth.api', 'admin.api:corporate-email.accounts.manage'])->prefix('corporate-email')->group(function () {
    // Cuentas de correo
    Route::get('accounts', [CorporateEmailController::class, 'accountsIndex']);
    Route::post('accounts', [CorporateEmailController::class, 'storeAccount']);
    Route::put('accounts/{account}', [CorporateEmailController::class, 'updateAccount']);
    Route::delete('accounts/{account}', [CorporateEmailController::class, 'destroyAccount']);

    // Pruebas y sincronizacion
    Route::post('accounts/{account}/test-connection', [CorporateEmailController::class, 'testConnection']);
    Route::post('accounts/{account}/sync', [CorporateEmailController::class, 'syncInbox']);

    // Mensajes
    Route::get('messages', [CorporateEmailController::class, 'messagesIndex']);
    Route::get('messages/{message}', [CorporateEmailController::class, 'showMessage']);
    Route::post('messages/{message}/mark-read', [CorporateEmailController::class, 'markAsRead']);

    // Envio
    Route::post('send', [CorporateEmailController::class, 'sendMessage']);
});
// ============================================================
// CMS - Sistema de Contenido Administrable
// ============================================================

use App\Http\Controllers\CmsPageController;
use App\Http\Controllers\CmsPostController;
use App\Http\Controllers\CmsFieldGroupController;
use App\Http\Controllers\CmsFieldValueController;
use App\Http\Controllers\CmsMenuController;
use App\Http\Controllers\CmsSiteSettingController;

// Rutas públicas del CMS (sin autenticación)
Route::prefix('public/cms')->group(function () {
    // Páginas públicas
    Route::get('pages/{slug}', [CmsPageController::class, 'showPublic']);

    // Blog / Posts públicos
    Route::get('posts', [CmsPostController::class, 'indexPublic']);
    Route::get('posts/categories', [CmsPostController::class, 'categoriesPublic']);
    Route::get('posts/tags', [CmsPostController::class, 'tagsPublic']);
    Route::get('posts/{slug}', [CmsPostController::class, 'showPublic']);

    // Menús públicos
    Route::get('menus/{slug}', [CmsMenuController::class, 'showPublic']);

    // Site Settings públicos
    Route::get('settings', [CmsSiteSettingController::class, 'allPublic']);
    Route::get('settings/{group}', [CmsSiteSettingController::class, 'groupPublic']);
});

// Rutas protegidas del CMS (admin)
Route::middleware(['auth.api', 'admin.api:cms.view|cms.manage'])->prefix('cms')->group(function () {
    // -- Páginas --
    Route::get('pages', [CmsPageController::class, 'index']);
    Route::get('pages/{page}', [CmsPageController::class, 'show'])->where('page', '[0-9]+');
    Route::post('pages', [CmsPageController::class, 'store'])->middleware('admin.api:cms.manage');
    Route::put('pages/{page}', [CmsPageController::class, 'update'])->where('page', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::patch('pages/{page}', [CmsPageController::class, 'update'])->where('page', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::delete('pages/{page}', [CmsPageController::class, 'destroy'])->where('page', '[0-9]+')->middleware('admin.api:cms.manage');

    // -- Posts --
    Route::get('posts', [CmsPostController::class, 'index']);
    Route::get('posts/{post}', [CmsPostController::class, 'show'])->where('post', '[0-9]+');
    Route::post('posts', [CmsPostController::class, 'store'])->middleware('admin.api:cms.manage');
    Route::put('posts/{post}', [CmsPostController::class, 'update'])->where('post', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::patch('posts/{post}', [CmsPostController::class, 'update'])->where('post', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::delete('posts/{post}', [CmsPostController::class, 'destroy'])->where('post', '[0-9]+')->middleware('admin.api:cms.manage');

    // -- Field Groups --
    Route::get('field-groups', [CmsFieldGroupController::class, 'index']);
    Route::get('field-groups/{fieldGroup}', [CmsFieldGroupController::class, 'show'])->where('fieldGroup', '[0-9]+');
    Route::post('field-groups', [CmsFieldGroupController::class, 'store'])->middleware('admin.api:cms.manage');
    Route::put('field-groups/{fieldGroup}', [CmsFieldGroupController::class, 'update'])->where('fieldGroup', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::patch('field-groups/{fieldGroup}', [CmsFieldGroupController::class, 'update'])->where('fieldGroup', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::delete('field-groups/{fieldGroup}', [CmsFieldGroupController::class, 'destroy'])->where('fieldGroup', '[0-9]+')->middleware('admin.api:cms.manage');

    // -- Field Definitions (dentro del controlador de field groups) --
    Route::post('field-definitions', [CmsFieldGroupController::class, 'storeField'])->middleware('admin.api:cms.manage');
    Route::put('field-definitions/{id}', [CmsFieldGroupController::class, 'updateField'])->middleware('admin.api:cms.manage');
    Route::delete('field-definitions/{id}', [CmsFieldGroupController::class, 'destroyField'])->middleware('admin.api:cms.manage');
    Route::post('field-definitions/reorder', [CmsFieldGroupController::class, 'reorderFields'])->middleware('admin.api:cms.manage');

    // -- Field Values --
    Route::get('field-values/{entityType}/{entityId}', [CmsFieldValueController::class, 'index']);
    Route::put('field-values/{entityType}/{entityId}', [CmsFieldValueController::class, 'update'])->middleware('admin.api:cms.manage');
    Route::post('field-values/repeater-row', [CmsFieldValueController::class, 'addRepeaterRow'])->middleware('admin.api:cms.manage');
    Route::delete('field-values/repeater-row/{id}', [CmsFieldValueController::class, 'deleteRepeaterRow'])->middleware('admin.api:cms.manage');

    // -- Menús --
    Route::get('menus', [CmsMenuController::class, 'index']);
    Route::get('menus/{menu}', [CmsMenuController::class, 'show'])->where('menu', '[0-9]+');
    Route::post('menus', [CmsMenuController::class, 'store'])->middleware('admin.api:cms.manage');
    Route::put('menus/{menu}', [CmsMenuController::class, 'update'])->where('menu', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::patch('menus/{menu}', [CmsMenuController::class, 'update'])->where('menu', '[0-9]+')->middleware('admin.api:cms.manage');
    Route::delete('menus/{menu}', [CmsMenuController::class, 'destroy'])->where('menu', '[0-9]+')->middleware('admin.api:cms.manage');

    // -- Menu Items --
    Route::post('menu-items', [CmsMenuController::class, 'storeItem'])->middleware('admin.api:cms.manage');
    Route::put('menu-items/{id}', [CmsMenuController::class, 'updateItem'])->middleware('admin.api:cms.manage');
    Route::delete('menu-items/{id}', [CmsMenuController::class, 'destroyItem'])->middleware('admin.api:cms.manage');
    Route::post('menu-items/reorder', [CmsMenuController::class, 'reorderItems'])->middleware('admin.api:cms.manage');

    // -- Site Settings --
    Route::get('settings', [CmsSiteSettingController::class, 'index']);
    Route::get('settings/group/{group}', [CmsSiteSettingController::class, 'byGroup']);
    Route::post('settings', [CmsSiteSettingController::class, 'store'])->middleware('admin.api:cms.manage');
    Route::put('settings/bulk', [CmsSiteSettingController::class, 'bulkUpdate'])->middleware('admin.api:cms.manage');
    Route::put('settings/{key}', [CmsSiteSettingController::class, 'updateByKey'])->middleware('admin.api:cms.manage');
    Route::delete('settings/{id}', [CmsSiteSettingController::class, 'destroy'])->where('id', '[0-9]+')->middleware('admin.api:cms.manage');
});
