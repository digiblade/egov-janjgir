<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Models\Concerns\ScopesPublicLifecycle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingResource extends Model
{
    use HasBilingualFields;
    use ScopesPublicLifecycle;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    public const TYPES = ['video', 'webinar', 'presentation', 'manual', 'faq', 'image', 'other'];

    protected $fillable = [
        'slug',
        'category_id',
        'resource_type',
        'title_hi',
        'title_en',
        'description_hi',
        'description_en',
        'media_asset_id',
        'thumbnail_media_id',
        'external_url',
        'duration_seconds',
        'training_date',
        'published_at',
        'expires_at',
        'display_order',
        'status',
        'uploaded_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'training_date' => 'date',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'display_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TrainingCategory::class, 'category_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'thumbnail_media_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
