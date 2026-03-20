<?php

namespace App\Enums;

enum ProductState: string
{
    case AVAILABLE            = 'available';
    case SOLD                 = 'sold';
    case RESERVED             = 'reserved';
    case RETURNED             = 'returned';
    case DEFECTIVE            = 'defective';
    case IN_REPAIR            = 'in_repair';
    case RETURNED_TO_SUPPLIER = 'returned_to_supplier';
    case TRADE_IN             = 'trade_in';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE            => 'Disponible',
            self::SOLD                 => 'Vendu',
            self::RESERVED             => 'Réservé',
            self::RETURNED             => 'Retourné',
            self::DEFECTIVE            => 'Défectueux',
            self::IN_REPAIR            => 'En réparation',
            self::RETURNED_TO_SUPPLIER => 'Renvoyé fournisseur',
            self::TRADE_IN              => 'Reprise',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE            => 'success',
            self::SOLD                 => 'info',
            self::RESERVED             => 'warning',
            self::RETURNED             => 'warning',
            self::DEFECTIVE            => 'danger',
            self::IN_REPAIR            => 'danger',
            self::RETURNED_TO_SUPPLIER => 'ghost',
            self::TRADE_IN             => 'primary',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::AVAILABLE            => [self::SOLD, self::RESERVED, self::DEFECTIVE, self::IN_REPAIR],
            self::RESERVED             => [self::AVAILABLE, self::SOLD],
            self::SOLD                 => [self::RETURNED, self::DEFECTIVE],
            self::RETURNED             => [self::AVAILABLE, self::DEFECTIVE, self::IN_REPAIR],
            self::DEFECTIVE            => [self::IN_REPAIR, self::RETURNED_TO_SUPPLIER],
            self::IN_REPAIR            => [self::AVAILABLE, self::DEFECTIVE],
            self::RETURNED_TO_SUPPLIER => [],
            self::TRADE_IN             => [self::AVAILABLE, self::DEFECTIVE],
        };
    }

    public function canTransitionTo(self $newState): bool
    {
        return in_array($newState, $this->allowedTransitions());
    }
}
