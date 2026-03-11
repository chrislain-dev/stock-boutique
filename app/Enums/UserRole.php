<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN   = 'admin';
    case VENDEUR = 'vendeur';

    public function canSeePrixAchat(): bool
    {
        return $this === self::ADMIN;
    }

    public function canAnnulerVente(): bool
    {
        return $this === self::ADMIN;
    }

    public function canAjusterStock(): bool
    {
        return $this === self::ADMIN;
    }
}
