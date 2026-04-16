<?php

namespace Database\Seeders;

use App\Enums\BranchType;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Clube Matriz Central',
                'slug' => 'matriz-central',
                'type' => BranchType::Headquarters,
                'email' => 'matriz@clube.test',
                'phone' => '(11) 3000-1000',
                'address' => 'Av. Central, 100 - Sao Paulo/SP',
                'monthly_fee_default' => 189.90,
            ],
            [
                'name' => 'Clube Filial Zona Sul',
                'slug' => 'zona-sul',
                'type' => BranchType::Branch,
                'email' => 'zonasul@clube.test',
                'phone' => '(11) 3000-2000',
                'address' => 'Rua das Palmeiras, 250 - Sao Paulo/SP',
                'monthly_fee_default' => 169.90,
            ],
            [
                'name' => 'Clube Filial Alphaville',
                'slug' => 'alphaville',
                'type' => BranchType::Branch,
                'email' => 'alphaville@clube.test',
                'phone' => '(11) 3000-3000',
                'address' => 'Av. dos Lagos, 900 - Barueri/SP',
                'monthly_fee_default' => 199.90,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(
                ['slug' => $branch['slug']],
                [...$branch, 'is_active' => true]
            );
        }
    }
}
