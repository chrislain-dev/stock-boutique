<?php

namespace App\Enums;

enum ProductCondition: string
{
    case SEALED      = 'sealed';
    case REFURBISHED = 'refurbished';
    case USED        = 'used';
    case DEFECTIVE   = 'defective';

    public function label(): string
    {
        return match ($this) {
            self::SEALED      => 'Scellé',
            self::REFURBISHED => 'Reconditionné',
            self::USED        => 'Occasion',
            self::DEFECTIVE   => 'Défectueux',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SEALED      => 'success',
            self::REFURBISHED => 'info',
            self::USED        => 'warning',
            self::DEFECTIVE   => 'danger',
        };
    }
}
