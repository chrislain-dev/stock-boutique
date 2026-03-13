<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogService;

class UserObserver
{
    public function created(User $user): void
    {
        ActivityLogService::log(
            action: 'create',
            description: "Utilisateur créé — {$user->name} ({$user->role->value})",
            model: $user,
        );
    }

    public function updated(User $user): void
    {
        $dirty = $user->getDirty();
        unset($dirty['password'], $dirty['remember_token'], $dirty['updated_at']);
        if (empty($dirty)) return;

        ActivityLogService::log(
            action: 'update',
            description: "Utilisateur modifié — {$user->name}",
            model: $user,
            oldValues: array_intersect_key($user->getOriginal(), $dirty),
            newValues: $dirty,
        );
    }

    public function deleted(User $user): void
    {
        ActivityLogService::log(
            action: 'delete',
            description: "Utilisateur supprimé — {$user->name}",
            model: $user,
        );
    }
}
