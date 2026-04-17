<?php

namespace Tests\Feature;

use App\Enums\ReservationStatus;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\ResourceBlock;
use App\Models\ResourceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReservationAvailabilityTest extends TestCase
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

    public function test_daily_availability_endpoint_returns_slots_and_marks_reserved_times_as_unavailable(): void
    {
        $admin = User::factory()->adminMatrix()->create();
        $branch = Branch::factory()->create();
        $resource = ClubResource::factory()->create([
            'branch_id' => $branch->id,
        ]);
        $member = $this->createActiveMember($branch);
        $date = Carbon::create(2026, 4, 20);

        ResourceSchedule::factory()->create([
            'club_resource_id' => $resource->id,
            'day_of_week' => $date->dayOfWeek,
            'opens_at' => '09:00',
            'closes_at' => '12:00',
            'slot_interval_minutes' => 60,
        ]);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'club_resource_id' => $resource->id,
            'member_id' => $member->id,
            'reserver_type' => Member::class,
            'reserver_id' => $member->id,
            'reservation_date' => $date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => ReservationStatus::Confirmed,
            'created_by_user_id' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->getJson("/api/v1/resources/{$resource->id}/availability?date={$date->toDateString()}");

        $response
            ->assertOk()
            ->assertJsonPath('slots.0.start_time', '09:00')
            ->assertJsonPath('slots.0.available', true)
            ->assertJsonPath('slots.1.start_time', '10:00')
            ->assertJsonPath('slots.1.available', false)
            ->assertJsonPath('slots.2.start_time', '11:00')
            ->assertJsonPath('slots.2.available', true);
    }

    public function test_monthly_availability_endpoint_summarizes_past_available_partial_and_unavailable_days(): void
    {
        $admin = User::factory()->adminMatrix()->create();
        $branch = Branch::factory()->create();
        $resource = ClubResource::factory()->create([
            'branch_id' => $branch->id,
        ]);
        $member = $this->createActiveMember($branch);

        collect(range(0, 6))->each(function (int $day) use ($resource) {
            ResourceSchedule::factory()->create([
                'club_resource_id' => $resource->id,
                'day_of_week' => $day,
                'opens_at' => '09:00',
                'closes_at' => '12:00',
                'slot_interval_minutes' => 60,
            ]);
        });

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'club_resource_id' => $resource->id,
            'member_id' => $member->id,
            'reserver_type' => Member::class,
            'reserver_id' => $member->id,
            'reservation_date' => '2026-04-20',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => ReservationStatus::Confirmed,
            'created_by_user_id' => $admin->id,
        ]);

        ResourceBlock::factory()->create([
            'club_resource_id' => $resource->id,
            'branch_id' => $branch->id,
            'block_date' => '2026-04-19',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'blocked_by_user_id' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->getJson("/api/v1/resources/{$resource->id}/availability/month?month=2026-04");

        $response->assertOk();

        $days = collect($response->json('days'))->keyBy('date');

        $this->assertSame('past', $days['2026-04-15']['state']);
        $this->assertSame('available', $days['2026-04-18']['state']);
        $this->assertSame(3, $days['2026-04-18']['available_slots_count']);
        $this->assertSame('unavailable', $days['2026-04-19']['state']);
        $this->assertSame(0, $days['2026-04-19']['available_slots_count']);
        $this->assertSame('partial', $days['2026-04-20']['state']);
        $this->assertSame(2, $days['2026-04-20']['available_slots_count']);
    }

    public function test_reservation_creation_rejects_an_unavailable_slot_with_a_form_error(): void
    {
        $admin = User::factory()->adminMatrix()->create();
        $branch = Branch::factory()->create();
        $resource = ClubResource::factory()->create([
            'branch_id' => $branch->id,
        ]);
        $member = $this->createActiveMember($branch, [
            'guest_limit_per_reservation' => 4,
        ]);
        $date = Carbon::create(2026, 4, 20);

        ResourceSchedule::factory()->create([
            'club_resource_id' => $resource->id,
            'day_of_week' => $date->dayOfWeek,
            'opens_at' => '09:00',
            'closes_at' => '12:00',
            'slot_interval_minutes' => 60,
        ]);

        Reservation::factory()->create([
            'branch_id' => $branch->id,
            'club_resource_id' => $resource->id,
            'member_id' => $member->id,
            'reserver_type' => Member::class,
            'reserver_id' => $member->id,
            'reservation_date' => $date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => ReservationStatus::Confirmed,
            'created_by_user_id' => $admin->id,
        ]);

        $response = $this
            ->from('/reservas/create')
            ->actingAs($admin)
            ->post('/reservas', [
                'club_resource_id' => $resource->id,
                'member_id' => $member->id,
                'reservation_date' => $date->toDateString(),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'guest_count' => 0,
            ]);

        $response
            ->assertRedirect('/reservas/create')
            ->assertSessionHasErrors([
                'reservation' => 'O horario selecionado nao esta disponivel.',
            ]);

        $this->assertSame(1, Reservation::query()->count());
    }

    public function test_member_reservation_create_page_only_lists_resources_from_linked_branches(): void
    {
        $primaryBranch = Branch::factory()->create(['name' => 'Centro']);
        $additionalBranch = Branch::factory()->create(['name' => 'Lago']);
        $outsideBranch = Branch::factory()->create(['name' => 'Serra']);
        $user = User::factory()->create();
        $member = $this->createActiveMember($primaryBranch, user: $user);
        $member->additionalBranches()->sync([$additionalBranch->id]);

        ClubResource::factory()->create([
            'branch_id' => $primaryBranch->id,
            'name' => 'Quadra Centro',
        ]);
        ClubResource::factory()->create([
            'branch_id' => $additionalBranch->id,
            'name' => 'Salao Lago',
        ]);
        ClubResource::factory()->create([
            'branch_id' => $outsideBranch->id,
            'name' => 'Piscina Serra',
        ]);

        $this->actingAs($user)
            ->get('/reservas/create')
            ->assertOk()
            ->assertSee('Quadra Centro - Centro')
            ->assertSee('Salao Lago - Lago')
            ->assertDontSee('Piscina Serra - Serra');
    }

    public function test_dependent_reservation_create_page_only_lists_resources_from_own_branch(): void
    {
        $memberBranch = Branch::factory()->create(['name' => 'Centro']);
        $dependentBranch = Branch::factory()->create(['name' => 'Lago']);
        $outsideBranch = Branch::factory()->create(['name' => 'Serra']);
        $member = $this->createActiveMember($memberBranch);
        $user = User::factory()->dependent()->create();

        Dependent::factory()->create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'branch_id' => $dependentBranch->id,
        ]);

        ClubResource::factory()->create([
            'branch_id' => $memberBranch->id,
            'name' => 'Quadra Centro',
        ]);
        ClubResource::factory()->create([
            'branch_id' => $dependentBranch->id,
            'name' => 'Salao Lago',
        ]);
        ClubResource::factory()->create([
            'branch_id' => $outsideBranch->id,
            'name' => 'Piscina Serra',
        ]);

        $this->actingAs($user)
            ->get('/reservas/create')
            ->assertOk()
            ->assertSee('Salao Lago - Lago')
            ->assertDontSee('Quadra Centro - Centro')
            ->assertDontSee('Piscina Serra - Serra');
    }

    public function test_admin_matrix_can_still_filter_reservation_resources_by_branch_query(): void
    {
        $admin = User::factory()->adminMatrix()->create();
        $firstBranch = Branch::factory()->create(['name' => 'Centro']);
        $secondBranch = Branch::factory()->create(['name' => 'Lago']);

        ClubResource::factory()->create([
            'branch_id' => $firstBranch->id,
            'name' => 'Quadra Centro',
        ]);
        ClubResource::factory()->create([
            'branch_id' => $secondBranch->id,
            'name' => 'Salao Lago',
        ]);

        $this->actingAs($admin)
            ->get("/reservas/create?branch_id={$secondBranch->id}")
            ->assertOk()
            ->assertSee('Salao Lago - Lago')
            ->assertDontSee('Quadra Centro - Centro');
    }

    public function test_member_cannot_access_daily_availability_for_a_resource_outside_linked_branches(): void
    {
        $linkedBranch = Branch::factory()->create();
        $outsideBranch = Branch::factory()->create();
        $user = User::factory()->create();
        $member = $this->createActiveMember($linkedBranch, user: $user);
        $member->additionalBranches()->sync([]);
        $resource = ClubResource::factory()->create([
            'branch_id' => $outsideBranch->id,
        ]);

        $this->actingAs($user)
            ->getJson("/api/v1/resources/{$resource->id}/availability?date=2026-04-20")
            ->assertForbidden();
    }

    public function test_dependent_cannot_access_monthly_availability_for_a_resource_outside_own_branch(): void
    {
        $memberBranch = Branch::factory()->create();
        $dependentBranch = Branch::factory()->create();
        $outsideBranch = Branch::factory()->create();
        $member = $this->createActiveMember($memberBranch);
        $user = User::factory()->dependent()->create();

        Dependent::factory()->create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'branch_id' => $dependentBranch->id,
        ]);

        $resource = ClubResource::factory()->create([
            'branch_id' => $outsideBranch->id,
        ]);

        $this->actingAs($user)
            ->getJson("/api/v1/resources/{$resource->id}/availability/month?month=2026-04")
            ->assertForbidden();
    }

    public function test_member_cannot_create_a_reservation_for_a_resource_outside_linked_branches(): void
    {
        $linkedBranch = Branch::factory()->create();
        $outsideBranch = Branch::factory()->create();
        $user = User::factory()->create();
        $this->createActiveMember($linkedBranch, [
            'guest_limit_per_reservation' => 2,
        ], $user);
        $resource = ClubResource::factory()->create([
            'branch_id' => $outsideBranch->id,
        ]);

        $response = $this
            ->from('/reservas/create')
            ->actingAs($user)
            ->post('/reservas', [
                'club_resource_id' => $resource->id,
                'reservation_date' => '2026-04-20',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'guest_count' => 0,
            ]);

        $response
            ->assertRedirect('/reservas/create')
            ->assertSessionHasErrors([
                'reservation' => 'Voce nao pode reservar recursos fora das filiais vinculadas ao seu cadastro.',
            ]);

        $this->assertSame(0, Reservation::query()->count());
    }

    protected function createActiveMember(Branch $branch, array $planOverrides = [], ?User $user = null): Member
    {
        $plan = Plan::factory()->create(array_merge([
            'guest_limit_per_reservation' => 2,
            'free_reservations_per_month' => 0,
        ], $planOverrides));

        return Member::factory()->create([
            'user_id' => $user?->id ?? User::factory(),
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);
    }
}
