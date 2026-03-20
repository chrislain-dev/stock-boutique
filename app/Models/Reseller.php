<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reseller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'phone_secondary',
        'address',
        'solde_du',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'solde_du'   => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ─── Accesseurs ───────────────────────────────────────────
    public function getHasDebtAttribute(): bool
    {
        return $this->solde_du > 0;
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('solde_du', '>', 0);
    }
}
