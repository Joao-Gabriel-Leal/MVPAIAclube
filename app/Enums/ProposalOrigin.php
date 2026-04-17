<?php

namespace App\Enums;

enum ProposalOrigin: string
{
    case Manual = 'manual';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Public => 'Adesao publica',
        };
    }
}
