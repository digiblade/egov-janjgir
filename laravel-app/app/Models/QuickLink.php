<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickLink extends Model
{
    use HasBilingualFields;
    use SoftDeletes;

    protected $fillable = [
        'title_hi',
        'title_en',
        'description_hi',
        'description_en',
        'icon',
        'url',
        'is_external',
        'opens_in_new_tab',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_external' => 'boolean',
            'opens_in_new_tab' => 'boolean',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
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
