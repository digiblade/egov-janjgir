<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqCategory extends Model
{
    use HasBilingualFields;
    use SoftDeletes;

    protected $fillable = [
        'name_hi', 'name_en', 'description_hi', 'description_en', 'slug',
        'display_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['display_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id')->orderBy('display_order');
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
