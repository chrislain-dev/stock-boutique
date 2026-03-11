<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH         = 'cash';
    case MOBILE_MONEY = 'mobile_money';
    case BANK_TRANSFER = 'bank_transfer';
    case CHEQUE       = 'cheque';
    case CARD         = 'card';
    case TRADE_IN     = 'trade_in';

    public function label(): string
    {
        return match ($this) {
            self::CASH          => 'Espèces',
            self::MOBILE_MONEY  => 'Mobile Money',
            self::BANK_TRANSFER => 'Virement bancaire',
            self::CHEQUE        => 'Chèque',
            self::CARD          => 'Carte bancaire',
            self::TRADE_IN      => 'Troc',
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [
            self::MOBILE_MONEY,
            self::BANK_TRANSFER,
            self::CHEQUE,
        ]);
    }
}
