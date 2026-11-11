<?php

namespace App\Models\Concerns;

trait HasBilingualFields
{
    public function localized(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $value = $this->getAttribute($field.'_'.($locale === 'hi' ? 'hi' : 'en'));

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        $fallbackLocale = $locale === 'hi' ? 'en' : 'hi';
        $fallback = $this->getAttribute($field.'_'.$fallbackLocale);

        return is_string($fallback) && trim($fallback) !== '' ? $fallback : null;
    }

    public function isUsingLocaleFallback(string $field, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();
        $requested = $this->getAttribute($field.'_'.($locale === 'hi' ? 'hi' : 'en'));
        $fallbackLocale = $locale === 'hi' ? 'en' : 'hi';
        $fallback = $this->getAttribute($field.'_'.$fallbackLocale);

        return (! is_string($requested) || trim($requested) === '')
            && is_string($fallback)
            && trim($fallback) !== '';
    }
}
