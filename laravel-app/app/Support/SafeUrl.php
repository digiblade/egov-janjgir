<?php

namespace App\Support;

final class SafeUrl
{
    public static function normalize(mixed $candidate): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $url = trim($candidate);

        if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url) === 1) {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        $parts = parse_url($url);

        if (! is_array($parts)
            || ! isset($parts['scheme'], $parts['host'])
            || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        return $url;
    }
}
