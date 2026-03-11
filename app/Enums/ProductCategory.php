<?php

namespace App\Enums;

enum ProductCategory: string
{
    case TELEPHONE  = 'telephone';
    case PC         = 'pc';
    case TABLETTE   = 'tablette';
    case ACCESSOIRE = 'accessoire';

    public function label(): string
    {
        return match ($this) {
            self::TELEPHONE  => 'Téléphone',
            self::PC         => 'PC / Laptop',
            self::TABLETTE   => 'Tablette',
            self::ACCESSOIRE => 'Accessoire',
        };
    }

    public function isSerialized(): bool
    {
        // Les accessoires ne sont PAS sérialisés par défaut
        return $this !== self::ACCESSOIRE;
    }
}
