<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
    protected $fillable = ['setting_key', 'value', 'is_sensitive', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_sensitive' => 'boolean',
        ];
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
