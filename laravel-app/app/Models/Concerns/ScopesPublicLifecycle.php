<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesPublicLifecycle
{
    public function isPubliclyVisibleNow(): bool
    {
        $published = $this->status === 'published'
            || ($this->status === 'scheduled' && $this->published_at?->lte(now()));

        return $published
            && (! array_key_exists('is_active', $this->attributes) || (bool) $this->is_active)
            && (! $this->published_at || $this->published_at->lte(now()))
            && (! $this->expires_at || $this->expires_at->gt(now()));
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $statusQuery): void {
                $statusQuery
                    ->where('status', 'published')
                    ->orWhere(function (Builder $scheduledQuery): void {
                        $scheduledQuery
                            ->where('status', 'scheduled')
                            ->whereNotNull('published_at')
                            ->where('published_at', '<=', now());
                    });
            })
            ->where(function (Builder $publicationQuery): void {
                $publicationQuery
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $expiryQuery): void {
                $expiryQuery->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
