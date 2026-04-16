<?php

namespace App\Enums;

enum BranchType: string
{
    case Headquarters = 'headquarters';
    case Branch = 'branch';

    public function label(): string
    {
        return match ($this) {
            self::Headquarters => 'Matriz',
            self::Branch => 'Filial',
        };
    }
}
