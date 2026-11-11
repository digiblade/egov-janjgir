<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSection extends Model
{
    use HasBilingualFields;

    protected $fillable = [
        'section_key',
        'label_en',
        'label_hi',
        'title_en',
        'title_hi',
        'description_en',
        'description_hi',
        'is_enabled',
        'display_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'display_order' => 'integer',
            'settings' => 'array',
        ];
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
