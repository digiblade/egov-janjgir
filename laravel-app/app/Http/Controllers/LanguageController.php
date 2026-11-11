<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /** @var list<string> */
    private const SUPPORTED_LOCALES = ['en', 'hi'];

    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, self::SUPPORTED_LOCALES, true), 404);

        $request->session()->put('locale', $locale);

        return redirect()->to($this->safeReturnUrl($request));
    }

    private function safeReturnUrl(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if (! is_string($referer) || $referer === '') {
            return url('/');
        }

        $parts = parse_url($referer);

        if (! is_array($parts)
            || ! isset($parts['scheme'], $parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return url('/');
        }

        $refererOrigin = strtolower($parts['scheme'].'://'.$parts['host']);
        $requestOrigin = strtolower($request->getScheme().'://'.$request->getHost());
        $refererPort = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
        $requestPort = $request->getPort();

        if ($refererOrigin !== $requestOrigin || $refererPort !== $requestPort) {
            return url('/');
        }

        $path = $parts['path'] ?? '/';

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return url('/');
        }

        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
