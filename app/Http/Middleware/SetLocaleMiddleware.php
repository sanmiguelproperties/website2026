<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Supported public locales.
     */
    private const SUPPORTED = ['es', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1) Explicit querystring (?lang=es|en or ?locale=es|en)
        $fromQuery = strtolower((string) ($request->query('lang') ?? $request->query('locale') ?? ''));
        if (in_array($fromQuery, self::SUPPORTED, true)) {
            $this->persistLocale($request, $fromQuery);
            return $fromQuery;
        }

        // 2) User session (web)
        if ($request->hasSession()) {
            $fromSession = strtolower((string) $request->session()->get('app_locale', ''));
            if (in_array($fromSession, self::SUPPORTED, true)) {
                return $fromSession;
            }
        }

        // 3) Explicit header (useful for public API)
        $fromHeader = strtolower((string) $request->header('X-Locale', ''));
        if (in_array($fromHeader, self::SUPPORTED, true)) {
            return $fromHeader;
        }

        // 4) Public frontend default
        return 'es';
    }

    private function persistLocale(Request $request, string $locale): void
    {
        if ($request->hasSession()) {
            $request->session()->put('app_locale', $locale);
        }
    }
}
