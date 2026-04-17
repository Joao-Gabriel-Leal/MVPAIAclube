<?php

namespace Tests\Feature;

use App\Enums\DependentStatus;
use App\Enums\InventoryMovementReason;
use App\Enums\InventoryMovementType;
use App\Enums\MembershipStatus;
use App\Enums\ProposalOrigin;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_admin_can_manage_inventory_and_dashboard_and_reports_include_new_metrics(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Norte']);
        $otherBranch = Branch::factory()->create(['name' => 'Clube Sul']);
        $admin = User::factory()->adminBranch($branch)->create();
        $plan = Plan::factory()->create();
        $resource = ClubResource::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Churrasqueira Norte',
        ]);

        Member::factory()->pending()->create([
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'source' => ProposalOrigin::Public,
        ]);

        $holder = Member::factory()->create([
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        Dependent::factory()->create([
            'member_id' => $holder->id,
            'branch_id' => $branch->id,
            'status' => DependentStatus::Pending,
            'source' => ProposalOrigin::Manual,
            'approved_at' => null,
        ]);

        $reservationMember = Member::factory()->create([
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        $reservation = Reservation::factory()->create([
            'branch_id' => $branch->id,
            'club_resource_id' => $resource->id,
            'member_id' => $reservationMember->id,
            'reserver_type' => Member::class,
            'reserver_id' => $reservationMember->id,
            'created_by_user_id' => $admin->id,
        ]);

        $createResponse = $this
            ->actingAs($admin)
            ->post(route('inventory.items.store'), [
                'branch_id' => $otherBranch->id,
                'club_resource_id' => $resource->id,
                'name' => 'Cloro Premium',
                'category' => 'Limpeza',
                'unit' => 'l',
                'current_quantity' => 5,
                'minimum_quantity' => 2,
                'is_active' => 1,
                'notes' => 'Uso na manutencao da piscina.',
            ]);

        $createResponse->assertRedirectContains('/estoque');

        $item = InventoryItem::query()->where('name', 'Cloro Premium')->sole();

        $this->assertSame($branch->id, $item->branch_id);
        $this->assertSame($resource->id, $item->club_resource_id);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.item_created',
            'auditable_id' => $item->id,
        ]);

        $movementResponse = $this
            ->actingAs($admin)
            ->post(route('inventory.movements.store', $item), [
                'movement_type' => InventoryMovementType::Exit->value,
                'reason' => InventoryMovementReason::ReservationConsumption->value,
                'quantity' => 3,
                'unit_cost' => 12.5,
                'club_resource_id' => $resource->id,
                'reservation_id' => $reservation->id,
                'occurred_at' => now()->format('Y-m-d H:i:s'),
                'notes' => 'Consumo na reserva da churrasqueira.',
            ]);

        $movementResponse->assertRedirectContains('/estoque');

        $item->refresh();
        $movement = InventoryMovement::query()->where('inventory_item_id', $item->id)->sole();

        $this->assertSame(2.0, (float) $item->current_quantity);
        $this->assertSame($reservation->id, $movement->reservation_id);
        $this->assertSame(InventoryMovementType::Exit, $movement->movement_type);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'inventory.movement_recorded',
            'auditable_id' => $movement->id,
        ]);

        $this->actingAs($admin)
            ->get('/estoque?low_stock_only=1')
            ->assertOk()
            ->assertSeeText('Cloro Premium');

        $this->actingAs($admin)
            ->getJson('/api/v1/reports?proposal_origin=public&inventory_category=Limpeza')
            ->assertOk()
            ->assertJsonPath('summary.pendingProposals', 1)
            ->assertJsonPath('summary.inventoryAlerts', 1)
            ->assertJsonPath('summary.reservationLinkedConsumption', 3);

        $dashboardCards = $this
            ->actingAs($admin)
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->json('cards');

        $this->assertSame(2, $dashboardCards['Propostas pendentes']);
        $this->assertSame(1, $dashboardCards['Itens em alerta']);
    }

    public function test_branch_scope_is_enforced_for_inventory_listing_and_movements(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $admin = User::factory()->adminBranch($branchA)->create();

        $foreignItem = InventoryItem::factory()->create([
            'branch_id' => $branchB->id,
            'name' => 'Item Exclusivo da Serra',
        ]);

        $this->actingAs($admin)
            ->get('/estoque?branch_id='.$branchB->id)
            ->assertOk()
            ->assertDontSeeText('Item Exclusivo da Serra');

        $this->actingAs($admin)
            ->post(route('inventory.movements.store', $foreignItem), [
                'movement_type' => InventoryMovementType::Exit->value,
                'reason' => InventoryMovementReason::InternalUse->value,
                'quantity' => 1,
            ])
            ->assertForbidden();
    }
}
