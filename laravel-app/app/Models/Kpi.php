<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use App\Support\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use HasBilingualFields;
    use SoftDeletes;

    protected $fillable = [
        'title_hi',
        'title_en',
        'description_hi',
        'description_en',
        'value',
        'prefix',
        'suffix',
        'icon',
        'background_color',
        'text_color',
        'icon_color',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function presentationStyle(): string
    {
        $background = Color::hex($this->background_color, '#FFFFFF');
        $text = Color::readableText($background, $this->text_color);
        $icon = Color::readableText($background, $this->icon_color ?: $text);

        return '--kpi-background:'.$background.';'
            .'--kpi-text:'.$text.';'
            .'--kpi-icon:'.$icon.';';
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
