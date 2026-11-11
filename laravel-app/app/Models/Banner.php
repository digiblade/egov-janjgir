<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Support\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasBilingualFields;
    use SoftDeletes;

    protected $fillable = [
        'title_hi',
        'title_en',
        'subtitle_hi',
        'subtitle_en',
        'desktop_image_media_id',
        'mobile_image_media_id',
        'cta_text_hi',
        'cta_text_en',
        'cta_url',
        'text_alignment',
        'text_color',
        'overlay_color',
        'overlay_opacity',
        'display_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'overlay_opacity' => 'integer',
            'display_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $startQuery): void {
                $startQuery->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $endQuery): void {
                $endQuery->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte(now()))
            && (! $this->ends_at || $this->ends_at->gt(now()));
    }

    public function presentationStyle(): string
    {
        $opacity = max(0, min(85, (int) $this->overlay_opacity)) / 100;

        return '--banner-text-color:'.Color::hex($this->text_color, '#FFFFFF').';'
            .'--banner-overlay-color:'.Color::hex($this->overlay_color, '#000000').';'
            .'--banner-overlay-opacity:'.$opacity.';';
    }

    public function desktopImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'desktop_image_media_id');
    }

    public function mobileImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'mobile_image_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
