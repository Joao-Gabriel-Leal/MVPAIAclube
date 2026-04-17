<?php

namespace App\Enums;

enum InventoryMovementReason: string
{
    case Purchase = 'purchase';
    case ReservationConsumption = 'reservation_consumption';
    case InternalUse = 'internal_use';
    case Loss = 'loss';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Compra',
            self::ReservationConsumption => 'Consumo em reserva',
            self::InternalUse => 'Uso interno',
            self::Loss => 'Perda',
        };
    }
}
