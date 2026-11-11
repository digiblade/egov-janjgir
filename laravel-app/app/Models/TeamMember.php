<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Models\Concerns\ScopesPublicLifecycle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use HasBilingualFields;
    use ScopesPublicLifecycle;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    protected $fillable = [
        'slug',
        'full_name_hi',
        'full_name_en',
        'photograph_media_id',
        'email',
        'email_public',
        'registration_number',
        'designation_hi',
        'designation_en',
        'team_category_id',
        'office_block',
        'contact_number',
        'contact_number_public',
        'responsibilities_hi',
        'responsibilities_en',
        'display_order',
        'is_active',
        'status',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'email_public' => 'boolean',
            'contact_number_public' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeVisibleInDirectory(Builder $query): Builder
    {
        return $query
            ->publiclyVisible()
            ->where('is_active', true)
            ->where(function (Builder $photoQuery): void {
                $photoQuery
                    ->whereNull('photograph_media_id')
                    ->orWhereHas('photograph', fn (Builder $mediaQuery) => $mediaQuery->where('visibility', 'public'));
            })
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TeamCategory::class, 'team_category_id');
    }

    public function photograph(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'photograph_media_id');
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
