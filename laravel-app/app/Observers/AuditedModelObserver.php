<?php

namespace App\Observers;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditedModelObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, array_keys($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $this->record('updated', $model, array_keys($model->getChanges()));
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, ['deleted_at']);
    }

    private function record(string $event, Model $model, array $fields): void
    {
        if (in_array($model->getTable(), ['audit_logs', 'portal_setup'], true)) {
            return;
        }

        $action = 'model.'.$event;
        if ($model instanceof \App\Models\MediaAsset) {
            $action = match (true) {
                $event === 'created' => 'media.uploaded',
                $event === 'deleted' => 'media.deleted',
                $event === 'updated' && in_array('visibility', $fields, true) => 'media.visibility_changed',
                default => 'media.updated',
            };
        }

        app(AuditLogger::class)->record(
            $action,
            $model,
            array_values(array_diff($fields, ['created_at', 'updated_at', 'deleted_at'])),
        );
    }
}
