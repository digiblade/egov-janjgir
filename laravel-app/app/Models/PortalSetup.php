<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalSetup extends Model
{
    protected $table = 'portal_setup';

    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $fillable = ['completed_at', 'completed_by'];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
