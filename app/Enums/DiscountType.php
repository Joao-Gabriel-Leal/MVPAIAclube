<?php

namespace App\Enums;

enum DiscountType: string
{
    case None = 'none';
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sem desconto',
            self::Percentage => 'Percentual',
            self::Fixed => 'Valor fixo',
        };
    }
}
