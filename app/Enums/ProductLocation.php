<?php

namespace App\Enums;

enum ProductLocation: string
{
    case STORE       = 'store';
    case TRANSIT     = 'transit';
    case CLIENT      = 'client';
    case RESELLER    = 'reseller';
    case REPAIR_SHOP = 'repair_shop';

    public function label(): string
    {
        return match ($this) {
            self::STORE       => 'Boutique',
            self::TRANSIT     => 'Transit',
            self::CLIENT      => 'Chez le client',
            self::RESELLER    => 'Chez le revendeur',
            self::REPAIR_SHOP => 'En réparation',
        };
    }
}
