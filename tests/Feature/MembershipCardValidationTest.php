<?php

namespace Tests\Feature;

use App\Enums\DependentStatus;
use App\Enums\MembershipStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipCardValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_validation_page_shows_member_card_information(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 16, 10, 30, 45));
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);

        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $plan = Plan::factory()->create(['name' => 'Plano Ouro']);
        $user = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Maria Silva',
            'cpf' => '12345678901',
            'card_suffix' => 'AB12CD',
            'card_public_token' => 'membervalidationtoken001',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        $this->get(route('cards.show', $user->card_public_token))
            ->assertOk()
            ->assertSee('Carteirinha validada')
            ->assertSee('Maria Silva')
            ->assertSee('Associado')
            ->assertSee('CS-AB12CD')
            ->assertSee('Clube Centro')
            ->assertSee('Ativo')
            ->assertSee('16/04/2026 10:30:45');

        Carbon::setTestNow();
    }

    public function test_public_validation_page_shows_member_status_variations(): void
    {
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);
        $branch = Branch::factory()->create();
        $plan = Plan::factory()->create();

        foreach ([
            ['suffix' => 'PD1234', 'token' => 'memberstatuspendingtoken', 'status' => MembershipStatus::Pending, 'label' => 'Pendente'],
            ['suffix' => 'CN5678', 'token' => 'memberstatuscanceltoken', 'status' => MembershipStatus::Cancelled, 'label' => 'Cancelado'],
            ['suffix' => 'DL9012', 'token' => 'memberstatusdelinqtoken0', 'status' => MembershipStatus::Delinquent, 'label' => 'Inadimplente'],
        ] as $index => $scenario) {
            $user = User::factory()->create([
                'role' => UserRole::Member,
                'name' => 'Associado '.$index,
                'email' => "associado{$index}@teste.local",
                'card_suffix' => $scenario['suffix'],
                'card_public_token' => $scenario['token'],
            ]);

            Member::factory()->create([
                'user_id' => $user->id,
                'primary_branch_id' => $branch->id,
                'plan_id' => $plan->id,
                'status' => $scenario['status'],
            ]);

            $this->get(route('cards.show', $scenario['token']))
                ->assertOk()
                ->assertSee($scenario['label']);
        }
    }

    public function test_public_validation_page_shows_dependent_status_variations(): void
    {
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);
        $branch = Branch::factory()->create(['name' => 'Clube Sul']);
        $plan = Plan::factory()->create();
        $holder = User::factory()->create([
            'role' => UserRole::Member,
            'card_suffix' => 'HOLD12',
            'card_public_token' => 'holdervalidationtoken001',
        ]);
        $member = Member::factory()->create([
            'user_id' => $holder->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        foreach ([
            ['suffix' => 'DP1111', 'token' => 'dependentstatusactive001', 'status' => DependentStatus::Active, 'label' => 'Ativo'],
            ['suffix' => 'DP2222', 'token' => 'dependentstatuspending01', 'status' => DependentStatus::Pending, 'label' => 'Pendente'],
            ['suffix' => 'DP3333', 'token' => 'dependentstatuscancel001', 'status' => DependentStatus::Cancelled, 'label' => 'Cancelado'],
        ] as $index => $scenario) {
            $user = User::factory()->dependent()->create([
                'name' => 'Dependente '.$index,
                'email' => "dependente{$index}@teste.local",
                'card_suffix' => $scenario['suffix'],
                'card_public_token' => $scenario['token'],
            ]);

            Dependent::factory()->create([
                'user_id' => $user->id,
                'member_id' => $member->id,
                'branch_id' => $branch->id,
                'relationship' => 'Filho',
                'status' => $scenario['status'],
            ]);

            $this->get(route('cards.show', $scenario['token']))
                ->assertOk()
                ->assertSee('Dependente')
                ->assertSee('Clube Sul')
                ->assertSee($scenario['label']);
        }
    }

    public function test_invalid_public_token_returns_not_found_state(): void
    {
        $this->get(route('cards.show', 'token-inexistente'))
            ->assertNotFound()
            ->assertSee('Carteirinha nao encontrada');
    }
}
