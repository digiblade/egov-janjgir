<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportRequest extends Model
{
    public const STATUSES = ['new', 'in_progress', 'resolved', 'closed'];

    protected $fillable = [
        'reference_number',
        'full_name',
        'email',
        'mobile_number',
        'subject',
        'message',
        'category',
        'status',
        'assigned_to',
        'submitted_at',
        'resolved_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'in_progress'])->whereNull('archived_at');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SupportNote::class)->latest();
    }
}
