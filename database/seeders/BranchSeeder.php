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
                'name' => 'Clube AABB',
                'slug' => 'clube-aabb',
                'type' => BranchType::Headquarters,
                'email' => 'contato@clubeaabb.demo',
                'phone' => '(61) 3000-0001',
                'address' => 'Base institucional demonstrativa da rede AABB - Brasilia/DF',
                'monthly_fee_default' => 189.90,
                'settings' => [
                    'city' => 'Rede nacional',
                    'summary' => 'Matriz institucional demonstrativa da operacao do Clube AABB.',
                    'public_phone' => '6130000001',
                    'public_whatsapp' => '61999990001',
                    'public_hours' => 'Seg a Sex, 09h as 18h',
                ],
            ],
            [
                'name' => 'AABB Brasilia',
                'slug' => 'brasilia',
                'type' => BranchType::Branch,
                'email' => 'contato@aabbdf.com.br',
                'phone' => '(61) 3223-0078',
                'address' => 'Setor de Clubes Esportivos Sul, Trecho 02, Conj. 16/17 - Brasilia/DF',
                'monthly_fee_default' => 199.90,
                'settings' => [
                    'city' => 'Brasilia',
                    'summary' => 'Unidade com parque aquatico, areas esportivas e agenda social ativa.',
                    'public_phone' => '6132230078',
                    'public_whatsapp' => '61999990078',
                    'public_hours' => 'Ter a Dom, 08h as 22h',
                ],
            ],
            [
                'name' => 'AABB Sao Paulo',
                'slug' => 'sao-paulo',
                'type' => BranchType::Branch,
                'email' => 'secretaria@aabbsp.com.br',
                'phone' => '(11) 5511-9555',
                'address' => 'Estrada de Itapecerica, 1935 - Santo Amaro, Sao Paulo/SP',
                'monthly_fee_default' => 199.90,
                'settings' => [
                    'city' => 'Sao Paulo',
                    'summary' => 'Sede Sul com estrutura esportiva, cultural e social para toda a familia.',
                    'public_phone' => '1155119555',
                    'public_whatsapp' => '11999999555',
                    'public_hours' => 'Seg a Dom, 07h as 22h',
                ],
            ],
            [
                'name' => 'AABB Rio de Janeiro',
                'slug' => 'rio-de-janeiro',
                'type' => BranchType::Branch,
                'email' => 'secretaria@aabb-rio.com.br',
                'phone' => '(21) 2274-4722',
                'address' => 'Av. Borges de Medeiros, 829 - Leblon, Rio de Janeiro/RJ',
                'monthly_fee_default' => 219.90,
                'settings' => [
                    'city' => 'Rio de Janeiro',
                    'summary' => 'Clube com tradicao no Leblon, foco em convivio, esportes e eventos.',
                    'public_phone' => '2122744722',
                    'public_whatsapp' => '21999994722',
                    'public_hours' => 'Seg a Dom, 08h as 21h',
                ],
            ],
            [
                'name' => 'AABB Salvador',
                'slug' => 'salvador',
                'type' => BranchType::Branch,
                'email' => 'secretaria@aabbsalvador.com.br',
                'phone' => '(71) 2106-8250',
                'address' => 'Rua Deputado Paulo Jackson, 869 - Piata, Salvador/BA',
                'monthly_fee_default' => 189.90,
                'settings' => [
                    'city' => 'Salvador',
                    'summary' => 'Unidade com lazer, esportes aquaticos, eventos e estrutura de hospedagem.',
                    'public_phone' => '7121068250',
                    'public_whatsapp' => '71999998250',
                    'public_hours' => 'Seg a Dom, 08h as 21h',
                ],
            ],
            [
                'name' => 'AABB Sao Luis',
                'slug' => 'sao-luis',
                'type' => BranchType::Branch,
                'email' => 'adm@aabbslz.com',
                'phone' => '(98) 3235-6924',
                'address' => 'Av. dos Holandeses, 8 - Calhau, Sao Luis/MA',
                'monthly_fee_default' => 179.90,
                'settings' => [
                    'city' => 'Sao Luis',
                    'summary' => 'Clube com parque aquatico, estrutura para eventos e programacao esportiva intensa.',
                    'public_phone' => '9832356924',
                    'public_whatsapp' => '98999996924',
                    'public_hours' => 'Seg a Dom, 08h as 21h',
                ],
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
