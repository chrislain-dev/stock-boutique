<?php

namespace App\Enums;

enum StockMovementType: string
{
    case STOCK_IN         = 'stock_in';
    case SALE_OUT         = 'sale_out';
    case CLIENT_RETURN    = 'client_return';
    case SUPPLIER_RETURN  = 'supplier_return';
    case TRANSFER         = 'transfer';
    case ADJUSTMENT       = 'adjustment';
    case LOSS             = 'loss';

    public function label(): string
    {
        return match ($this) {
            self::STOCK_IN        => 'Entrée stock',
            self::SALE_OUT        => 'Sortie vente',
            self::CLIENT_RETURN   => 'Retour client',
            self::SUPPLIER_RETURN => 'Retour fournisseur',
            self::TRANSFER        => 'Transfert',
            self::ADJUSTMENT      => 'Ajustement',
            self::LOSS            => 'Perte / Vol',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [
            self::STOCK_IN,
            self::CLIENT_RETURN,
            self::ADJUSTMENT,
        ]);
    }
}
