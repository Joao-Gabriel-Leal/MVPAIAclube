<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_portal_only_lists_own_invoices_and_exposes_benefits_and_receipts(): void
    {
        $branch = Branch::factory()->create(['name' => 'Filial do Titular']);
        $foreignBranch = Branch::factory()->create(['name' => 'Filial Externa']);
        $plan = Plan::factory()->create(['name' => 'Plano Clube Ouro']);
        $resource = ClubResource::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Churrasqueira Central',
        ]);
        $plan->resources()->attach($resource);

        $memberUser = User::factory()->create([
            'name' => 'Titular Portal',
            'email' => 'titular.portal@example.com',
        ]);

        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        $foreignMember = Member::factory()->create([
            'primary_branch_id' => $foreignBranch->id,
        ]);

        $ownPaidInvoice = MembershipInvoice::factory()->create([
            'branch_id' => $branch->id,
            'member_id' => $member->id,
            'billing_period' => '2026-04-01',
            'due_date' => '2026-04-10',
            'amount' => 150,
            'paid_amount' => 150,
            'status' => InvoiceStatus::Paid,
            'paid_at' => now()->subDay(),
        ]);

        $ownPendingInvoice = MembershipInvoice::factory()->create([
            'branch_id' => $branch->id,
            'member_id' => $member->id,
            'billing_period' => '2026-05-01',
            'due_date' => '2026-05-10',
            'amount' => 155,
            'status' => InvoiceStatus::Pending,
        ]);

        $foreignInvoice = MembershipInvoice::factory()->create([
            'branch_id' => $foreignBranch->id,
            'member_id' => $foreignMember->id,
            'billing_period' => '2026-06-01',
            'due_date' => '2026-06-10',
            'amount' => 199,
            'status' => InvoiceStatus::Paid,
            'paid_amount' => 199,
            'paid_at' => now()->subDay(),
        ]);

        $this->actingAs($memberUser)
            ->get('/faturas')
            ->assertOk()
            ->assertSeeText('Filial do Titular')
            ->assertSeeText('04/2026')
            ->assertSeeText('05/2026')
            ->assertDontSeeText('Filial Externa');

        $this->actingAs($memberUser)
            ->get(route('member-invoices.show', $ownPendingInvoice))
            ->assertOk()
            ->assertSeeText('Fatura 05/2026');

        $this->actingAs($memberUser)
            ->get(route('member-invoices.receipt', $ownPaidInvoice))
            ->assertOk()
            ->assertSeeText('Comprovante de pagamento');

        $this->actingAs($memberUser)
            ->get(route('member-invoices.receipt', $ownPendingInvoice))
            ->assertNotFound();

        $this->actingAs($memberUser)
            ->get(route('member-invoices.show', $foreignInvoice))
            ->assertForbidden();

        $this->actingAs($memberUser)
            ->get('/beneficios')
            ->assertOk()
            ->assertSeeText('Plano Clube Ouro')
            ->assertSeeText('Churrasqueira Central');
    }

    public function test_dependent_portal_can_access_holder_invoices_but_not_other_members(): void
    {
        $branch = Branch::factory()->create();
        $holder = Member::factory()->create([
            'primary_branch_id' => $branch->id,
        ]);
        $otherMember = Member::factory()->create();

        $dependentUser = User::factory()->dependent()->create([
            'name' => 'Dependente Portal',
            'email' => 'dependente.portal@example.com',
        ]);

        Dependent::factory()->create([
            'user_id' => $dependentUser->id,
            'member_id' => $holder->id,
            'branch_id' => $branch->id,
        ]);

        $holderInvoice = MembershipInvoice::factory()->create([
            'branch_id' => $branch->id,
            'member_id' => $holder->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $otherInvoice = MembershipInvoice::factory()->create([
            'member_id' => $otherMember->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $this->actingAs($dependentUser)
            ->get(route('member-invoices.show', $holderInvoice))
            ->assertOk();

        $this->actingAs($dependentUser)
            ->get(route('member-invoices.show', $otherInvoice))
            ->assertForbidden();
    }
}
