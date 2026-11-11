<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class HomepageFeedItem
{
    public function __construct(
        public readonly string $title_en,
        public readonly string $title_hi,
        public readonly ?string $summary_en,
        public readonly ?string $summary_hi,
        public readonly ?string $url,
        public readonly ?Carbon $published_at,
        public readonly string $source,
        public readonly int|string $source_id,
    ) {}

    public function localized(string $field, ?string $locale = null): ?string
    {
        if (! in_array($field, ['title', 'summary'], true)) {
            return null;
        }

        $locale ??= app()->getLocale();
        $value = $this->{$field.'_'.($locale === 'hi' ? 'hi' : 'en')};

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        $fallback = $this->{$field.'_'.($locale === 'hi' ? 'en' : 'hi')};

        return is_string($fallback) && trim($fallback) !== '' ? $fallback : null;
    }
}
