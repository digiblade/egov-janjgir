<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentInformation extends Model
{
    use HasBilingualFields;

    protected $fillable = [
        'singleton_key',
        'title_hi',
        'title_en',
        'description_hi',
        'description_en',
        'mission_hi',
        'mission_en',
        'objectives_hi',
        'objectives_en',
        'image_media_id',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_media_id');
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
