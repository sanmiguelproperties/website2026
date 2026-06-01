<?php

use App\Services\PublicLocationMenuService;
use App\Services\CmsService;
use App\Services\HomeStatsService;
use App\Services\ZonePageService;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SellerLeadController;
use App\Models\CmsPage;
use App\Models\ZonePage;
use App\Models\MLSOffice;
use App\Models\MLSAgent;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PropertyLookupController;
use App\Http\Controllers\PropertyContactRequestController;
use App\Http\Controllers\VisitCalendarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| View Routes
|--------------------------------------------------------------------------
|
| Web view routes.
|
*/

$locale = static fn (): string => app()->getLocale();

$publicContext = static function (string $pageSlug, array $extra = []) use ($locale): array {
    $currentLocale = $locale();

    $base = [
        'locale' => $currentLocale,
        'pageData' => CmsService::getPageData($pageSlug, $currentLocale),
        'menu' => CmsService::getMenu('main-header'),
        'mlsLocationMenu' => PublicLocationMenuService::stateCityTree(),
        'settings' => CmsService::settings(['contact', 'social', 'general'], $currentLocale),
    ];

    return array_merge($base, $extra);
};

$isMlsOfficesPublicEnabled = static fn (): bool => CmsService::settingBoolean('public_show_mls_offices', true);
$isMlsAgentsPublicEnabled = static fn (): bool => CmsService::settingBoolean('public_show_mls_agents', true);

$ensureMlsOfficesPublicEnabled = static function () use ($isMlsOfficesPublicEnabled): void {
    abort_unless($isMlsOfficesPublicEnabled(), 404);
};

$ensureMlsAgentsPublicEnabled = static function () use ($isMlsAgentsPublicEnabled): void {
    abort_unless($isMlsAgentsPublicEnabled(), 404);
};

// Public locale switcher (ES/EN)
Route::get('/idioma/{locale}', function (Request $request, string $locale) {
    $normalized = strtolower($locale);

    if (!in_array($normalized, ['es', 'en'], true)) {
        $normalized = 'es';
    }

    $request->session()->put('app_locale', $normalized);

    return redirect()->back();
})->name('public.locale.switch');

// Home
Route::get('/', function () use ($publicContext) {
    $context = $publicContext('home');

    $context['homeStats'] = HomeStatsService::make(
        $context['pageData'] ?? null,
        $context['locale'] ?? app()->getLocale()
    );
    $context['homeSaleStats'] = HomeStatsService::saleCta(
        $context['locale'] ?? app()->getLocale()
    );

    return view('home', $context);
})->name('home');

// Public listings
Route::get('/propiedades', function () use ($publicContext) {
    return view('public.properties-index', $publicContext('properties'));
})->name('public.properties.index');

Route::get('/zonas/{zoneSlug}', function (string $zoneSlug) use ($publicContext, $locale) {
    $zonePage = ZonePage::query()
        ->where('slug', $zoneSlug)
        ->where('is_active', true)
        ->first();

    if (!$zonePage) {
        ZonePageService::syncFromPublishedProperties();
        $zonePage = ZonePage::query()
            ->where('slug', $zoneSlug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    $currentLocale = $locale();
    $zoneTitle = $zonePage->title($currentLocale);
    $zoneDescription = $zonePage->description($currentLocale);

    return view('public.properties-index', $publicContext('properties', [
        'zonePage' => $zonePage,
        'zoneInitialFilters' => [
            'region' => $zonePage->region,
            'city' => $zonePage->city,
            'city_area' => $zonePage->city_area,
        ],
        'seoTitleOverride' => $zonePage->metaTitle($currentLocale) ?: $zoneTitle,
        'seoDescriptionOverride' => $zonePage->metaDescription($currentLocale) ?: $zoneDescription,
    ]));
})->name('public.zones.show');

Route::get('/zones/{zoneSlug}', function (string $zoneSlug) {
    return redirect()->route('public.zones.show', ['zoneSlug' => $zoneSlug], 301);
})->name('public.zones.legacy-show');

Route::get('/favoritas', function () use ($publicContext) {
    return view('public.properties-favorites', $publicContext('properties-favorites'));
})->name('public.properties.favorites');

Route::get('/agencias', function () use ($publicContext, $ensureMlsOfficesPublicEnabled) {
    $ensureMlsOfficesPublicEnabled();

    return view('public.mls-offices-index', $publicContext('mls-offices'));
})->name('public.mls-offices.index');

Route::get('/agentes', function () use ($publicContext, $ensureMlsAgentsPublicEnabled) {
    $ensureMlsAgentsPublicEnabled();

    return view('public.mls-agents-index', $publicContext('mls-agents'));
})->name('public.mls-agents.index');

// Legacy compatibility
Route::get('/mls-offices', function () use ($ensureMlsOfficesPublicEnabled) {
    $ensureMlsOfficesPublicEnabled();

    return redirect('/agencias', 301);
})->name('public.mls-offices.legacy-index');

Route::get('/agencias/{mlsOfficeId}', function (string $mlsOfficeId) use ($publicContext, $ensureMlsOfficesPublicEnabled) {
    $ensureMlsOfficesPublicEnabled();

    return view('public.mls-office-detail', $publicContext('mls-office-detail', [
        'mlsOfficeId' => (int) $mlsOfficeId,
    ]));
})
    ->where('mlsOfficeId', '[0-9]+')
    ->name('public.mls-offices.show');

Route::get('/agentes/{mlsAgentId}', function (string $mlsAgentId) use ($publicContext, $ensureMlsAgentsPublicEnabled) {
    $ensureMlsAgentsPublicEnabled();

    return view('public.mls-agent-detail', $publicContext('mls-agent-detail', [
        'mlsAgentId' => (int) $mlsAgentId,
    ]));
})
    ->where('mlsAgentId', '[0-9]+')
    ->name('public.mls-agents.show');

Route::get('/mls-offices/{mlsOfficeId}', function (string $mlsOfficeId) use ($ensureMlsOfficesPublicEnabled) {
    $ensureMlsOfficesPublicEnabled();

    return redirect('/agencias/' . (int) $mlsOfficeId, 301);
})
    ->where('mlsOfficeId', '[0-9]+')
    ->name('public.mls-offices.legacy-show');

Route::get('/mls-agents', function () use ($ensureMlsAgentsPublicEnabled) {
    $ensureMlsAgentsPublicEnabled();

    return redirect('/agentes', 301);
})->name('public.mls-agents.legacy-index');

Route::get('/mls-agents/{mlsAgentId}', function (string $mlsAgentId) use ($ensureMlsAgentsPublicEnabled) {
    $ensureMlsAgentsPublicEnabled();

    return redirect('/agentes/' . (int) $mlsAgentId, 301);
})
    ->where('mlsAgentId', '[0-9]+')
    ->name('public.mls-agents.legacy-show');

Route::get('/propiedades/{propertyId}', function (string $propertyId) use ($publicContext) {
    return view('public.property-detail', $publicContext('property-detail', [
        'propertyId' => (int) $propertyId,
    ]));
})
    ->where('propertyId', '[0-9]+')
    ->name('public.properties.show');

// Contact and About pages
Route::get('/contacto', function () use ($publicContext) {
    $context = $publicContext('contact');
    return view('public.contact', $context);
})->name('public.contact');

Route::get('/vendedores/vende-con-nosotros', function () use ($publicContext) {
    $context = $publicContext('sell-with-us');
    return view('public.sell-with-us', $context);
})->name('public.sell-with-us');

Route::get('/vende-con-nosotros', function () {
    return redirect()->route('public.sell-with-us', [], 301);
})->name('public.sell-with-us.short');

Route::get('/sell-with-us', function () {
    return redirect()->route('public.sell-with-us', [], 301);
})->name('public.sell-with-us.en');

Route::post('/vendedores/vende-con-nosotros/leads', [SellerLeadController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('public.sell-with-us.leads.store');

Route::get('/nosotros', function () use ($publicContext) {
    $primaryOffice = MLSOffice::query()
        ->where('is_primary', true)
        ->orderBy('mls_office_id')
        ->first();

    $primaryOfficeAgents = collect();

    if ($primaryOffice) {
        $primaryOfficeAgents = MLSAgent::query()
            ->with(['photoMediaAsset'])
            ->where('is_active', true)
            ->where('mls_office_id', (int) $primaryOffice->mls_office_id)
            ->orderBy('name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    $context = $publicContext('about', [
        'primaryOffice' => $primaryOffice,
        'primaryOfficeAgents' => $primaryOfficeAgents,
    ]);

    return view('public.about', $context);
})->name('about');

Route::redirect('/equipo', '/nosotros#equipo', 301)->name('public.team');
Route::redirect('/team', '/nosotros#equipo', 301)->name('public.team.legacy');

// Auth view routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login')->middleware('guest');

// Protected view routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('admin:dashboard.view');

    Route::prefix('admin')->group(function () {
        Route::get('/funnel', function () {
            return view('funnel');
        })->name('funnel')->middleware('admin:leads.view|pipelines.view');

        Route::get('/users', function () {
            return view('users.manage');
        })->name('users')->middleware('admin:users.view');

        Route::get('/rbac', function () {
            return view('rbac.manage');
        })->name('rbac')->middleware('admin:rbac.manage');

        Route::get('/currencies', function () {
            return view('currencies.manage');
        })->name('currencies')->middleware('admin:settings.manage');

        Route::get('/properties', function () {
            return view('properties.manage');
        })->name('properties')->middleware('admin:properties.view');
        Route::get('/properties/search', [PropertyLookupController::class, 'search'])
            ->name('properties.search')
            ->middleware('admin:clients.view|calendar.view|properties.view|leads.view|leads.create|leads.edit|leads.edit.own');

        Route::get('/property-contact-requests', [PropertyContactRequestController::class, 'index'])
            ->name('property-contact-requests')
            ->middleware('admin:leads.view');
        Route::post('/property-contact-requests', [PropertyContactRequestController::class, 'manualStore'])
            ->name('property-contact-requests.store')
            ->middleware('admin:leads.create');
        Route::patch('/property-contact-requests/{contactRequest}', [PropertyContactRequestController::class, 'update'])
            ->name('property-contact-requests.update')
            ->middleware('admin:leads.view');
        Route::post('/property-contact-requests/{contactRequest}/convert-client', [PropertyContactRequestController::class, 'convertToClient'])
            ->name('property-contact-requests.convert-client')
            ->middleware('admin:leads.view');
        Route::get('/calendar', [VisitCalendarController::class, 'index'])
            ->name('calendar')
            ->middleware('admin:calendar.view');
        Route::post('/calendar/visits', [VisitCalendarController::class, 'store'])
            ->name('calendar.visits.store')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::patch('/calendar/visits/{visit}', [VisitCalendarController::class, 'update'])
            ->where('visit', '[0-9]+')
            ->name('calendar.visits.update')
            ->middleware('admin:calendar.view');

        Route::get('/zones', function () {
            return view('zones.manage');
        })->name('zones')->middleware('admin:settings.manage');

        Route::get('/clients', [ClientController::class, 'index'])
            ->name('clients')
            ->middleware('admin:clients.view');
        Route::get('/clients/{client}', [ClientController::class, 'show'])
            ->where('client', '[0-9]+')
            ->name('clients.show')
            ->middleware('admin:clients.view');
        Route::patch('/clients/{client}', [ClientController::class, 'update'])
            ->where('client', '[0-9]+')
            ->name('clients.update')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::post('/clients/{client}/comments', [ClientController::class, 'storeComment'])
            ->where('client', '[0-9]+')
            ->name('clients.comments.store')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::patch('/clients/{client}/comments/{comment}', [ClientController::class, 'updateComment'])
            ->where(['client' => '[0-9]+', 'comment' => '[0-9]+'])
            ->name('clients.comments.update')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::delete('/clients/{client}/comments/{comment}', [ClientController::class, 'destroyComment'])
            ->where(['client' => '[0-9]+', 'comment' => '[0-9]+'])
            ->name('clients.comments.destroy')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::post('/clients/{client}/visits', [ClientController::class, 'storeVisit'])
            ->where('client', '[0-9]+')
            ->name('clients.visits.store')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::patch('/clients/{client}/visits/{visit}', [ClientController::class, 'updateVisit'])
            ->where(['client' => '[0-9]+', 'visit' => '[0-9]+'])
            ->name('clients.visits.update')
            ->middleware('admin:clients.edit|clients.edit.own');
        Route::delete('/clients/{client}/visits/{visit}', [ClientController::class, 'destroyVisit'])
            ->where(['client' => '[0-9]+', 'visit' => '[0-9]+'])
            ->name('clients.visits.destroy')
            ->middleware('admin:clients.edit|clients.edit.own');

        Route::get('/color-themes', function () {
            return view('color-themes.manage');
        })->name('color-themes')->middleware('admin:settings.manage');

        Route::get('/frontend-colors', function () {
            return view('frontend-colors.manage');
        })->name('frontend-colors')->middleware('admin:settings.manage');

        Route::get('/easybroker', function () {
            return view('easybroker.sync');
        })->name('easybroker')->middleware('admin:integrations.view');

        Route::get('/easybroker/mls-export', function () {
            return view('easybroker.mls-export');
        })->name('easybroker.mls-export')->middleware('admin:integrations.sync');

        Route::get('/mls', function () {
            return view('mls.sync');
        })->name('mls')->middleware('admin:integrations.view');

        Route::get('/mls-agents', function () {
            return view('mls-agents.manage');
        })->name('mls-agents')->middleware('admin:catalogs.manage');

        Route::get('/mls-offices', function () {
            return view('mls-offices.manage');
        })->name('mls-offices')->middleware('admin:catalogs.manage');

        Route::get('/correos/configuracion', function () {
            return view('emails.configuration');
        })->name('corporate-email.configuration')->middleware('admin:corporate-email.accounts.manage');

        Route::get('/correos/bandeja', function () {
            return view('emails.inbox');
        })->name('corporate-email.inbox')->middleware('admin:corporate-email.view');

        Route::get('/correos/salida', function () {
            return view('emails.outbox');
        })->name('corporate-email.outbox')->middleware('admin:corporate-email.view');

        Route::get('/correos/redactar', function () {
            return view('emails.compose');
        })->name('corporate-email.compose')->middleware('admin:corporate-email.send');

        Route::get('/notifications', function () {
            return view('notifications.manage');
        })->name('notifications')->middleware('admin:notifications.view');

        Route::get('/tutorials', function () {
            return view('tutorials.index');
        })->name('tutorials')->middleware('admin:tutorials.view');

        Route::get('/tutorial-videos', function () {
            return view('tutorial-videos.manage');
        })->name('tutorial-videos')->middleware('admin:tutorials.manage');

        Route::get('/manual', function () {
            return view('manual.index');
        })->name('manual')->middleware('admin:manual.view');

        Route::get('/manual-articles', function () {
            return view('manual.manage');
        })->name('manual-articles')->middleware('admin:manual.manage');

        // CMS
        Route::get('/cms/pages', function () {
            return view('cms.pages.manage');
        })->name('cms.pages')->middleware('admin:cms.view');

        Route::get('/cms/posts', function () {
            return view('cms.posts.manage');
        })->name('cms.posts')->middleware('admin:cms.view');

        Route::get('/cms/menus', function () {
            return view('cms.menus.manage');
        })->name('cms.menus')->middleware('admin:cms.view');

        Route::get('/cms/settings', function () {
            return view('cms.settings.manage');
        })->name('cms.settings')->middleware('admin:cms.view');

    });
});
