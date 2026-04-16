<?php

namespace App\Enums;

enum DependentStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Active => 'Ativo',
            self::Cancelled => 'Cancelado',
        };
    }
}
