<?php

namespace App\Models;

use App\Models\Concerns\ScopesPublicLifecycle;
use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
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
        'featured_image_media_id',
        'attachment_media_id',
        'published_at',
        'expires_at',
        'priority',
        'display_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'display_order' => 'integer',
        ];
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'featured_image_media_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'attachment_media_id');
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
