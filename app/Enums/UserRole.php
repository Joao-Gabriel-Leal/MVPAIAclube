<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminMatrix = 'admin_matrix';
    case AdminBranch = 'admin_branch';
    case Member = 'member';
    case Dependent = 'dependent';

    public function label(): string
    {
        return match ($this) {
            self::AdminMatrix => 'Admin Matriz',
            self::AdminBranch => 'Admin Filial',
            self::Member => 'Associado',
            self::Dependent => 'Dependente',
        };
    }
}
