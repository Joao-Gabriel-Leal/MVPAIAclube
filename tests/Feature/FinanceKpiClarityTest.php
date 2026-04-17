<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\MembershipInvoice;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinanceKpiClarityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 4, 17, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_finance_page_defaults_to_the_current_competency(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $admin = User::factory()->adminBranch($branch)->create();

        $this->createInvoice($branch, 'Titular Abril', '2026-04', 419.80);
        $this->createInvoice($branch, 'Titular Marco', '2026-03', 210.50);

        $this->actingAs($admin)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertSeeText('Mensalidades previstas')
            ->assertSeeText('Competencia 04/2026 | Clube Norte')
            ->assertSeeText('R$ 419,80')
            ->assertSeeText('Titular Abril')
            ->assertDontSeeText('Titular Marco');
    }

    public function test_finance_page_filters_cards_and_history_by_billing_period(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $admin = User::factory()->adminBranch($branch)->create();

        $this->createInvoice($branch, 'Titular Marco', '2026-03', 310.00);
        $this->createInvoice($branch, 'Titular Abril', '2026-04', 450.00);

        $this->actingAs($admin)
            ->get(route('finance.index', ['billing_period' => '2026-03']))
            ->assertOk()
            ->assertSeeText('Competencia 03/2026 | Clube Norte')
            ->assertSeeText('R$ 310,00')
            ->assertSeeText('Titular Marco')
            ->assertDontSeeText('Titular Abril')
            ->assertDontSeeText('R$ 450,00');
    }

    public function test_branch_finance_tab_keeps_competency_context_after_post_actions(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $admin = User::factory()->adminBranch($branch)->create();
        $existingInvoice = $this->createInvoice($branch, 'Titular Marco', '2026-03', 199.90);
        $existingInvoice->member->update(['status' => MembershipStatus::Pending]);
        $newMember = $this->createMember($branch, 'Titular Novo', 149.90);
        $branchFinanceUrl = route('filiais.show', [
            'branch' => $branch,
            'tab' => 'financeiro',
            'billing_period' => '2026-03',
        ]);

        $this->actingAs($admin)
            ->get($branchFinanceUrl)
            ->assertOk()
            ->assertSeeText('Mensalidades previstas')
            ->assertSeeText('Competencia 03/2026 | Clube Norte')
            ->assertSeeText('Titular Marco');

        $this->actingAs($admin)
            ->from($branchFinanceUrl)
            ->post(route('finance.mark-paid', $existingInvoice), [
                'paid_amount' => 199.90,
                'notes' => 'Baixa manual',
            ])
            ->assertRedirect($branchFinanceUrl);

        $this->actingAs($admin)
            ->from($branchFinanceUrl)
            ->post(route('finance.generate'), [
                'branch_id' => $branch->id,
                'billing_period' => '2026-03',
            ])
            ->assertRedirect($branchFinanceUrl);

        $this->assertDatabaseHas('membership_invoices', [
            'member_id' => $newMember->id,
            'branch_id' => $branch->id,
            'billing_period' => '2026-03-01 00:00:00',
        ]);
    }

    public function test_reports_page_shows_explicit_period_and_scope_for_membership_revenue(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $admin = User::factory()->adminBranch($branch)->create();

        $this->createInvoice($branch, 'Titular Marco', '2026-03', 419.80);
        $this->createInvoice($branch, 'Titular Abril', '2026-04', 210.00);

        $this->actingAs($admin)
            ->get(route('reports.index', [
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertSeeText('Mensalidades previstas no periodo')
            ->assertSeeText('Periodo 01/03/2026 a 31/03/2026 | Clube Norte')
            ->assertSeeText('R$ 419,80')
            ->assertDontSeeText('R$ 210,00');
    }

    public function test_branch_summary_card_explains_the_current_competency_scope(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $admin = User::factory()->adminMatrix()->create();

        $this->createInvoice($branch, 'Titular Abril', '2026-04', 419.80);
        $this->createInvoice($branch, 'Titular Marco', '2026-03', 210.00);

        $this->actingAs($admin)
            ->get(route('filiais.show', $branch))
            ->assertOk()
            ->assertSeeText('Mensalidades previstas')
            ->assertSeeText('Competencia 04/2026 | Clube Norte')
            ->assertSeeText('R$ 419,80');
    }

    private function createMember(Branch $branch, string $name, float $monthlyFee): Member
    {
        $user = User::factory()->create(['name' => $name]);
        $plan = Plan::factory()->create(['base_price' => $monthlyFee]);

        return Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'custom_monthly_fee' => $monthlyFee,
        ]);
    }

    private function createInvoice(
        Branch $branch,
        string $memberName,
        string $billingPeriod,
        float $amount,
        InvoiceStatus $status = InvoiceStatus::Pending,
    ): MembershipInvoice {
        $member = $this->createMember($branch, $memberName, $amount);
        $period = Carbon::createFromFormat('Y-m', $billingPeriod)->startOfMonth();

        return MembershipInvoice::factory()->create([
            'branch_id' => $branch->id,
            'member_id' => $member->id,
            'billing_period' => $period,
            'due_date' => $period->copy()->day(10),
            'amount' => $amount,
            'status' => $status,
        ]);
    }
}
