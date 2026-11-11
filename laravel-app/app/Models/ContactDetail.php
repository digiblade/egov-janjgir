<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactDetail extends Model
{
    use HasBilingualFields;

    protected $fillable = [
        'singleton_key',
        'department_name_hi',
        'department_name_en',
        'office_address_hi',
        'office_address_en',
        'support_email',
        'contact_number',
        'office_hours_hi',
        'office_hours_en',
        'map_url',
        'updated_by',
    ];

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
