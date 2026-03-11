<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role'              => UserRole::class,
        'is_active'         => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Helpers rôles ────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isVendeur(): bool
    {
        return $this->role === UserRole::VENDEUR;
    }

    public function hasPermission(string $permission): bool
    {
        return match ($permission) {
            'see_purchase_price' => $this->role->canSeePurchasePrice(),
            'see_profit'         => $this->role->canSeeProfit(),
            'cancel_sale'        => $this->role->canCancelSale(),
            'adjust_stock'       => $this->role->canAdjustStock(),
            'manage_users'       => $this->role->canManageUsers(),
            default              => false,
        };
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::ADMIN->value);
    }

    public function scopeVendeurs($query)
    {
        return $query->where('role', UserRole::VENDEUR->value);
    }
}
