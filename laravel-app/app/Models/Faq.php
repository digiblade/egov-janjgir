<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Models\Concerns\ScopesPublicLifecycle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasBilingualFields;
    use ScopesPublicLifecycle;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'scheduled', 'archived'];

    protected $fillable = [
        'category_id', 'question_hi', 'question_en', 'answer_hi', 'answer_en',
        'display_order', 'status', 'published_at', 'expires_at', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
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
