<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Associado Individual',
                'slug' => 'individual',
                'description' => 'Categoria demonstrativa para acesso individual a esporte, lazer e servicos essenciais do clube.',
                'base_price' => 129.90,
                'dependent_limit' => 1,
                'guest_limit_per_reservation' => 2,
                'free_reservations_per_month' => 0,
                'extra_reservation_discount_type' => DiscountType::None,
                'extra_reservation_discount_value' => 0,
                'dependents_inherit_benefits' => false,
            ],
            [
                'name' => 'Associado Familia',
                'slug' => 'familia',
                'description' => 'Categoria demonstrativa com foco em dependentes, convivencia e mais flexibilidade de uso.',
                'base_price' => 179.90,
                'dependent_limit' => 3,
                'guest_limit_per_reservation' => 5,
                'free_reservations_per_month' => 1,
                'extra_reservation_discount_type' => DiscountType::Percentage,
                'extra_reservation_discount_value' => 20,
                'dependents_inherit_benefits' => true,
            ],
            [
                'name' => 'Associado Comunidade',
                'slug' => 'comunidade',
                'description' => 'Categoria demonstrativa com maior alcance de beneficios e uso ampliado da estrutura.',
                'base_price' => 249.90,
                'dependent_limit' => 5,
                'guest_limit_per_reservation' => 8,
                'free_reservations_per_month' => 3,
                'extra_reservation_discount_type' => DiscountType::Percentage,
                'extra_reservation_discount_value' => 35,
                'dependents_inherit_benefits' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [...$plan, 'is_active' => true]
            );
        }
    }
}
