<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID    = 'paid';
    case PARTIAL = 'partial';
    case UNPAID  = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::PAID    => 'Payé',
            self::PARTIAL => 'Acompte versé',
            self::UNPAID  => 'Non payé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PAID    => 'success',
            self::PARTIAL => 'warning',
            self::UNPAID  => 'error',
        };
    }
}
