<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log(
        string  $action,
        string  $description,
        ?object $model      = null,
        ?array  $oldValues  = null,
        ?array  $newValues  = null,
    ): void {
        if (!auth()->check()) return;

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
