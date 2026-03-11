<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN   = 'admin';
    case VENDEUR = 'vendeur';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN   => 'Administrateur',
            self::VENDEUR => 'Vendeur',
        };
    }

    public function canSeePurchasePrice(): bool
    {
        return $this === self::ADMIN;
    }

    public function canSeeProfit(): bool
    {
        return $this === self::ADMIN;
    }

    public function canCancelSale(): bool
    {
        return $this === self::ADMIN;
    }

    public function canAdjustStock(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}
