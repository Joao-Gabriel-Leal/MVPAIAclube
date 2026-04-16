<?php

namespace Tests\Feature;

use App\Models\ClubResource;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_matrix_can_view_the_plan_index_page(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('plans.index'));

        $response->assertOk();
    }

    public function test_admin_matrix_can_create_a_plan_with_resources(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $resourceA = ClubResource::factory()->create();
        $resourceB = ClubResource::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('plans.store'), [
                'name' => 'Plano Familia',
                'description' => 'Plano para a familia toda.',
                'base_price' => '149.90',
                'dependent_limit' => 3,
                'guest_limit_per_reservation' => 2,
                'free_reservations_per_month' => 1,
                'extra_reservation_discount_type' => 'percentage',
                'extra_reservation_discount_value' => '15.00',
                'dependents_inherit_benefits' => '1',
                'is_active' => '1',
                'resource_ids' => [$resourceA->id, $resourceB->id],
            ]);

        $plan = Plan::query()->first();

        $response->assertRedirect(route('plans.edit', $plan));
        $this->assertNotNull($plan);
        $this->assertSame('Plano Familia', $plan->name);
        $this->assertSame(2, $plan->resources()->count());
    }

    public function test_admin_matrix_can_update_a_plan_and_deactivate_it(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $plan = Plan::factory()->create([
            'name' => 'Plano Ouro',
            'is_active' => true,
        ]);
        $resource = ClubResource::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put(route('plans.update', $plan), [
                'name' => 'Plano Ouro Plus',
                'description' => 'Versao atualizada do plano.',
                'base_price' => '219.90',
                'dependent_limit' => 4,
                'guest_limit_per_reservation' => 3,
                'free_reservations_per_month' => 2,
                'extra_reservation_discount_type' => 'fixed',
                'extra_reservation_discount_value' => '20.00',
                'dependents_inherit_benefits' => '0',
                'is_active' => '0',
                'resource_ids' => [$resource->id],
            ]);

        $response->assertRedirect(route('plans.edit', $plan));

        $plan->refresh();

        $this->assertSame('Plano Ouro Plus', $plan->name);
        $this->assertFalse($plan->is_active);
        $this->assertSame(1, $plan->resources()->count());
    }

    public function test_admin_matrix_cannot_delete_a_plan_with_members(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $plan = Plan::factory()->create();
        Member::factory()->create([
            'plan_id' => $plan->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('plans.destroy', $plan));

        $response
            ->assertRedirect(route('plans.edit', $plan))
            ->assertSessionHasErrors('delete');

        $this->assertModelExists($plan);
    }

    public function test_admin_matrix_can_delete_a_plan_without_members(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $plan = Plan::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('plans.destroy', $plan));

        $response->assertRedirect(route('plans.index'));
        $this->assertModelMissing($plan);
    }
}
