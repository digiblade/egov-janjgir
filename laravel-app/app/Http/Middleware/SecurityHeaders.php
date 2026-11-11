<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $sameOriginPdfPreview = $request->routeIs('media.public') && $request->boolean('preview');

        $directives = [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "style-src-attr 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-src 'self' https://www.youtube-nocookie.com",
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            $sameOriginPdfPreview ? "frame-ancestors 'self'" : "frame-ancestors 'none'",
        ];

        if (app()->environment('local')) {
            $directives[1] .= ' http://localhost:5173 http://127.0.0.1:5173';
            $directives[2] .= ' http://localhost:5173 http://127.0.0.1:5173';
            $directives[6] .= ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', $sameOriginPdfPreview ? 'SAMEORIGIN' : 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
