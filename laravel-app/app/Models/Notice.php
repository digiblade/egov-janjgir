<?php

namespace App\Models;

use App\Models\Concerns\ScopesPublicLifecycle;
use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
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
        'notice_type',
        'priority',
        'published_at',
        'expires_at',
        'attachment_media_id',
        'external_url',
        'is_pinned',
        'display_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_pinned' => 'boolean',
            'display_order' => 'integer',
        ];
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
