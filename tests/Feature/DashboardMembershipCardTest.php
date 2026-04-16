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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMembershipCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_sees_the_membership_card_on_the_dashboard(): void
    {
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);
        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $plan = Plan::factory()->create(['name' => 'Plano Premium']);
        $user = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Marina Costa',
            'cpf' => '12345678901',
            'card_suffix' => 'AB12CD',
            'card_public_token' => 'membertokendashboard001x',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Carteirinha digital')
            ->assertSee('CS-AB12CD')
            ->assertSee('Valide esta carteirinha')
            ->assertSee('Plano Premium')
            ->assertSee('Clube Centro');
    }

    public function test_dependent_sees_the_membership_card_on_the_dashboard(): void
    {
        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);
        $branch = Branch::factory()->create(['name' => 'Clube Sul']);
        $plan = Plan::factory()->create(['name' => 'Plano Familia']);
        $holder = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Carlos Titular',
            'card_suffix' => 'ZX34YU',
            'card_public_token' => 'holdertokendashboard0001',
        ]);

        $member = Member::factory()->create([
            'user_id' => $holder->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        $user = User::factory()->dependent()->create([
            'name' => 'Ana Dependente',
            'cpf' => '98765432100',
            'card_suffix' => 'QW12ER',
            'card_public_token' => 'dependentdashboardtoken1',
        ]);

        Dependent::factory()->create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'branch_id' => $branch->id,
            'relationship' => 'Filha',
            'status' => DependentStatus::Active,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Carteirinha digital')
            ->assertSee('CS-QW12ER')
            ->assertSee('Carlos Titular')
            ->assertSee('Filha')
            ->assertSee('Clube Sul');
    }

    public function test_admin_does_not_receive_the_member_dashboard_card_experience(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('filiais.index'));
    }
}
