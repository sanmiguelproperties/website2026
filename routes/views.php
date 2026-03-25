<?php

use App\Services\PublicLocationMenuService;
use App\Services\CmsService;
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
    return view('home', $context);
})->name('home');

// Public listings
Route::get('/propiedades', function () use ($publicContext) {
    return view('public.properties-index', $publicContext('properties'));
})->name('public.properties.index');

Route::get('/agencias', function () use ($publicContext) {
    return view('public.mls-offices-index', $publicContext('mls-offices'));
})->name('public.mls-offices.index');

Route::get('/agentes', function () use ($publicContext) {
    return view('public.mls-agents-index', $publicContext('mls-agents'));
})->name('public.mls-agents.index');

// Legacy compatibility
Route::redirect('/mls-offices', '/agencias', 301)->name('public.mls-offices.legacy-index');

Route::get('/agencias/{mlsOfficeId}', function (string $mlsOfficeId) use ($publicContext) {
    return view('public.mls-office-detail', $publicContext('mls-office-detail', [
        'mlsOfficeId' => (int) $mlsOfficeId,
    ]));
})
    ->where('mlsOfficeId', '[0-9]+')
    ->name('public.mls-offices.show');

Route::get('/agentes/{mlsAgentId}', function (string $mlsAgentId) use ($publicContext) {
    return view('public.mls-agent-detail', $publicContext('mls-agent-detail', [
        'mlsAgentId' => (int) $mlsAgentId,
    ]));
})
    ->where('mlsAgentId', '[0-9]+')
    ->name('public.mls-agents.show');

Route::redirect('/mls-offices/{mlsOfficeId}', '/agencias/{mlsOfficeId}', 301)
    ->where('mlsOfficeId', '[0-9]+')
    ->name('public.mls-offices.legacy-show');

Route::redirect('/mls-agents', '/agentes', 301)->name('public.mls-agents.legacy-index');

Route::redirect('/mls-agents/{mlsAgentId}', '/agentes/{mlsAgentId}', 301)
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

Route::get('/nosotros', function () use ($publicContext) {
    $context = $publicContext('about');
    return view('public.about', $context);
})->name('about');

// Auth view routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login')->middleware('guest');

// Protected view routes
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

        Route::get('/agencies', function () {
            return view('agencies.manage');
        })->name('agencies');

        Route::get('/color-themes', function () {
            return view('color-themes.manage');
        })->name('color-themes');

        Route::get('/frontend-colors', function () {
            return view('frontend-colors.manage');
        })->name('frontend-colors');

        Route::get('/easybroker', function () {
            return view('easybroker.sync');
        })->name('easybroker');

        Route::get('/easybroker/mls-export', function () {
            return view('easybroker.mls-export');
        })->name('easybroker.mls-export');

        Route::get('/mls', function () {
            return view('mls.sync');
        })->name('mls');

        Route::get('/mls-agents', function () {
            return view('mls-agents.manage');
        })->name('mls-agents');

        Route::get('/mls-offices', function () {
            return view('mls-offices.manage');
        })->name('mls-offices');

        Route::get('/corporate-email', function () {
            return view('emails.manage');
        })->name('corporate-email');

        // CMS
        Route::get('/cms/pages', function () {
            return view('cms.pages.manage');
        })->name('cms.pages');

        Route::get('/cms/posts', function () {
            return view('cms.posts.manage');
        })->name('cms.posts');

        Route::get('/cms/menus', function () {
            return view('cms.menus.manage');
        })->name('cms.menus');

        Route::get('/cms/settings', function () {
            return view('cms.settings.manage');
        })->name('cms.settings');
    });
});
