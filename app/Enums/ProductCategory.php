<?php

namespace App\Enums;

enum ProductCategory: string
{
    case TELEPHONE  = 'telephone';
    case PC         = 'pc';
    case TABLET     = 'tablet';
    case ACCESSORY  = 'accessory';

    public function label(): string
    {
        return match ($this) {
            self::TELEPHONE => 'Téléphone',
            self::PC        => 'PC / Laptop',
            self::TABLET    => 'Tablette',
            self::ACCESSORY => 'Accessoire',
        };
    }

    public function isSerialized(): bool
    {
        return $this !== self::ACCESSORY;
    }

    public function icon(): string
    {
        return match ($this) {
            self::TELEPHONE => 'device-phone-mobile',
            self::PC        => 'computer-desktop',
            self::TABLET    => 'device-tablet',
            self::ACCESSORY => 'puzzle-piece',
        };
    }
}
