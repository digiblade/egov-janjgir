<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Models\Concerns\ScopesPublicLifecycle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryAlbum extends Model
{
    use HasBilingualFields;
    use ScopesPublicLifecycle;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    protected $fillable = [
        'slug',
        'title_hi',
        'title_en',
        'description_hi',
        'description_en',
        'event_date',
        'cover_media_id',
        'gallery_category_id',
        'display_order',
        'is_active',
        'status',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeVisibleInGallery(Builder $query): Builder
    {
        return $query
            ->publiclyVisible()
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('display_order')->orderBy('id');
    }

    public function visibleImages(): HasMany
    {
        return $this->images()
            ->where('is_active', true)
            ->whereHas('media', fn (Builder $mediaQuery) => $mediaQuery->where('visibility', 'public'));
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
