<?php

namespace App\Enums;

enum ProductLocation: string
{
    case STORE       = 'store';
    case TRANSIT     = 'transit';
    case CLIENT      = 'client';
    case RESELLER    = 'reseller';
    case REPAIR_SHOP = 'repair_shop';
    case REPRISE     = 'reprise';
    case SUPPLIER_RETURN = 'supplier_return';


    public function label(): string
    {
        return match ($this) {
            self::STORE       => 'Boutique',
            self::TRANSIT     => 'Transit',
            self::CLIENT      => 'Chez le client',
            self::RESELLER    => 'Chez le revendeur',
            self::REPAIR_SHOP => 'En réparation',
            self::REPRISE     => 'Reprises / Troc',
            self::SUPPLIER_RETURN => 'À renvoyer fournisseur',
        };
    }
}
