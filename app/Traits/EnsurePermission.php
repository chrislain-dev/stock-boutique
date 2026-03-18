<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait for enforcing permissions in Livewire components
 */
trait EnsurePermission
{
    /**
     * Check if current user has the required permission.
     * Throws 403 exception if unauthorized.
     */
    protected function requirePermission(string $permission): void
    {
        if (!Auth::check()) {
            abort(401, 'Authentification requise.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPermission($permission)) {
            abort(403, "Permission refusée: {$permission}");
        }
    }

    /**
     * Check if current user is admin.
     * Throws 403 exception if not admin.
     */
    protected function requireAdmin(): void
    {
        if (!Auth::check()) {
            abort(401, 'Authentification requise.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Cette action est réservée aux administrateurs.');
        }
    }

    /**
     * Check if current user is authenticated (vendeur or admin).
     * Throws 401 exception if not authenticated.
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            abort(401, 'Authentification requise.');
        }
    }

    /**
     * Check if current user possesses a specific permission, return boolean.
     */
    protected function hasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->hasPermission($permission);
    }

    /**
     * Check if current user is admin, return boolean.
     */
    protected function isAdmin(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->isAdmin();
    }
}
