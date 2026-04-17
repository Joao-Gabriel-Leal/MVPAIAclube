<?php

namespace Tests\Feature;

use App\Enums\DependentStatus;
use App\Enums\MembershipStatus;
use App\Enums\ProposalOrigin;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProposalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 4, 16, 10, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_public_enrollment_creates_a_pending_public_proposal(): void
    {
        $branch = Branch::factory()->create();
        $plan = Plan::factory()->create();

        $response = $this->post(route('enrollment.store', $branch), [
            'plan_id' => $plan->id,
            'name' => 'Publico Recente',
            'cpf' => '12345678901',
            'birth_date' => '1990-05-10',
            'email' => 'publico.recente@example.com',
            'phone' => '11999999999',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));

        $member = Member::query()->with('user')->sole();

        $this->assertSame(MembershipStatus::Pending, $member->status);
        $this->assertSame(ProposalOrigin::Public, $member->source);
        $this->assertSame($branch->id, $member->primary_branch_id);
        $this->assertSame('publico.recente@example.com', $member->user->email);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'member.enrolled_publicly',
            'auditable_id' => $member->id,
        ]);
    }

    public function test_proposals_page_filters_the_unified_queue_and_status_actions_keep_using_existing_services(): void
    {
        $branchA = Branch::factory()->create(['name' => 'Clube Centro']);
        $branchB = Branch::factory()->create(['name' => 'Clube Serra']);
        $plan = Plan::factory()->create();
        $matrixAdmin = User::factory()->adminMatrix()->create();
        $branchAdmin = User::factory()->adminBranch($branchA)->create();

        $publicMember = Member::factory()->pending()->create([
            'user_id' => User::factory()->state([
                'name' => 'Publico Recente',
                'email' => 'publico.recente@example.com',
            ]),
            'primary_branch_id' => $branchA->id,
            'plan_id' => $plan->id,
            'source' => ProposalOrigin::Public,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $manualStaleMember = Member::factory()->pending()->create([
            'user_id' => User::factory()->state([
                'name' => 'Manual Antigo',
                'email' => 'manual.antigo@example.com',
            ]),
            'primary_branch_id' => $branchA->id,
            'plan_id' => $plan->id,
            'source' => ProposalOrigin::Manual,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        $holder = Member::factory()->create([
            'primary_branch_id' => $branchA->id,
            'plan_id' => $plan->id,
        ]);

        $pendingDependent = Dependent::factory()->create([
            'user_id' => User::factory()->dependent()->state([
                'name' => 'Dependente Meio',
                'email' => 'dependente.meio@example.com',
            ]),
            'member_id' => $holder->id,
            'branch_id' => $branchA->id,
            'status' => DependentStatus::Pending,
            'source' => ProposalOrigin::Manual,
            'approved_at' => null,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $otherBranchMember = Member::factory()->pending()->create([
            'user_id' => User::factory()->state([
                'name' => 'Outra Filial',
                'email' => 'outra.filial@example.com',
            ]),
            'primary_branch_id' => $branchB->id,
            'plan_id' => $plan->id,
            'source' => ProposalOrigin::Public,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->actingAs($matrixAdmin)
            ->get('/propostas?branch_id='.$branchA->id.'&type=member&origin=public&age=recent')
            ->assertOk()
            ->assertSeeText('Publico Recente')
            ->assertDontSeeText('Manual Antigo')
            ->assertDontSeeText('Dependente Meio')
            ->assertDontSeeText('Outra Filial');

        $approveResponse = $this
            ->actingAs($branchAdmin)
            ->post(route('members.status', $publicMember), [
                'status' => MembershipStatus::Active->value,
                'notes' => 'Aprovado pela fila de propostas.',
            ]);

        $approveResponse->assertRedirect(route('membros.show', $publicMember));

        $publicMember->refresh();

        $this->assertSame(MembershipStatus::Active, $publicMember->status);
        $this->assertStringContainsString('Aprovado pela fila de propostas.', $publicMember->notes ?? '');
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'member.status_updated',
            'auditable_id' => $publicMember->id,
        ]);

        $rejectResponse = $this
            ->actingAs($branchAdmin)
            ->post(route('dependents.status', $pendingDependent), [
                'status' => DependentStatus::Cancelled->value,
            ]);

        $rejectResponse->assertRedirect(route('membros.show', $holder).'#dependentes');

        $pendingDependent->refresh();

        $this->assertSame(DependentStatus::Cancelled, $pendingDependent->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'dependent.status_updated',
            'auditable_id' => $pendingDependent->id,
        ]);

        $manualStaleMember->refresh();
        $otherBranchMember->refresh();

        $this->assertSame(MembershipStatus::Pending, $manualStaleMember->status);
        $this->assertSame(MembershipStatus::Pending, $otherBranchMember->status);
    }

    public function test_proposals_page_shows_human_friendly_elapsed_time_labels(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $plan = Plan::factory()->create();
        $matrixAdmin = User::factory()->adminMatrix()->create();

        Member::factory()->pending()->create([
            'user_id' => User::factory()->state([
                'name' => 'Quase Um Dia',
                'email' => 'quase.um.dia@example.com',
            ]),
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'source' => ProposalOrigin::Public,
            'created_at' => now()->subHours(23),
            'updated_at' => now()->subHours(23),
        ]);

        $this->actingAs($matrixAdmin)
            ->get('/propostas')
            ->assertOk()
            ->assertSeeText('Quase Um Dia')
            ->assertSeeText('23 h')
            ->assertDontSeeText('0.95');
    }

    public function test_members_index_keeps_pending_records_out_of_the_administrative_base(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $plan = Plan::factory()->create();
        $branchAdmin = User::factory()->adminBranch($branch)->create();

        Member::factory()->create([
            'user_id' => User::factory()->state([
                'name' => 'Associado Ativo',
                'email' => 'ativo@example.com',
            ]),
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        Member::factory()->create([
            'user_id' => User::factory()->state([
                'name' => 'Associado Cancelado',
                'email' => 'cancelado@example.com',
            ]),
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Cancelled,
        ]);

        $holder = Member::factory()->create([
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Active,
        ]);

        Member::factory()->pending()->create([
            'user_id' => User::factory()->state([
                'name' => 'Associado Pendente',
                'email' => 'pendente@example.com',
            ]),
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        Dependent::factory()->create([
            'user_id' => User::factory()->dependent()->state([
                'name' => 'Dependente Ativo',
                'email' => 'dependente.ativo@example.com',
            ]),
            'member_id' => $holder->id,
            'branch_id' => $branch->id,
            'status' => DependentStatus::Active,
        ]);

        Dependent::factory()->create([
            'user_id' => User::factory()->dependent()->state([
                'name' => 'Dependente Cancelado',
                'email' => 'dependente.cancelado@example.com',
            ]),
            'member_id' => $holder->id,
            'branch_id' => $branch->id,
            'status' => DependentStatus::Cancelled,
        ]);

        Dependent::factory()->create([
            'user_id' => User::factory()->dependent()->state([
                'name' => 'Dependente Pendente',
                'email' => 'dependente.pendente@example.com',
            ]),
            'member_id' => $holder->id,
            'branch_id' => $branch->id,
            'status' => DependentStatus::Pending,
        ]);

        $this->actingAs($branchAdmin)
            ->get(route('membros.index'))
            ->assertOk()
            ->assertSeeText('Associado Ativo')
            ->assertSeeText('Dependente Ativo')
            ->assertDontSeeText('Associado Cancelado')
            ->assertDontSeeText('Associado Pendente')
            ->assertDontSeeText('Dependente Cancelado')
            ->assertDontSeeText('Dependente Pendente');

        $this->actingAs($branchAdmin)
            ->get(route('membros.index', ['status' => 'base']))
            ->assertOk()
            ->assertSeeText('Associado Ativo')
            ->assertSeeText('Associado Cancelado')
            ->assertSeeText('Dependente Ativo')
            ->assertSeeText('Dependente Cancelado')
            ->assertDontSeeText('Associado Pendente')
            ->assertDontSeeText('Dependente Pendente');
    }
}
