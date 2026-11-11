<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Support\PublicSiteSettings;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private const SUPPORTED_LOCALES = ['en', 'hi'];

    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale', 'en');

        if (! in_array($defaultLocale, self::SUPPORTED_LOCALES, true)) {
            $defaultLocale = 'en';
        }

        if (! $request->session()->has('locale')) {
            $defaultLocale = app(PublicSiteSettings::class)->all()['site.carousel']['default_language'] ?? $defaultLocale;
        }

        $locale = $request->session()->get('locale', $defaultLocale);

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
