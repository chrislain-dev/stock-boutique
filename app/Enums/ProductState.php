<?php

namespace App\Enums;

enum ProductState: string
{
    case AVAILABLE  = 'available';
    case SOLD       = 'sold';
    case RESERVED   = 'reserved';
    case RETURNED   = 'returned';
    case DEFECTIVE  = 'defective';
    case IN_REPAIR  = 'in_repair';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::SOLD      => 'Vendu',
            self::RESERVED  => 'Réservé',
            self::RETURNED  => 'Retourné',
            self::DEFECTIVE => 'Défectueux',
            self::IN_REPAIR => 'En réparation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::SOLD      => 'info',
            self::RESERVED  => 'warning',
            self::RETURNED  => 'warning',
            self::DEFECTIVE => 'danger',
            self::IN_REPAIR => 'danger',
        };
    }

    // Transitions autorisées depuis cet état
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::AVAILABLE => [self::SOLD, self::RESERVED, self::DEFECTIVE, self::IN_REPAIR],
            self::RESERVED  => [self::AVAILABLE, self::SOLD],
            self::SOLD      => [self::RETURNED],
            self::RETURNED  => [self::AVAILABLE, self::DEFECTIVE, self::IN_REPAIR],
            self::DEFECTIVE => [self::IN_REPAIR],
            self::IN_REPAIR => [self::AVAILABLE, self::DEFECTIVE],
        };
    }

    public function canTransitionTo(self $newState): bool
    {
        return in_array($newState, $this->allowedTransitions());
    }
}
