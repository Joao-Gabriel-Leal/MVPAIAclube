<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_can_be_rendered(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/filiais');
    }

    public function test_reservations_page_can_be_rendered(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get('/reservas')
            ->assertOk();
    }

    public function test_reservation_create_page_can_be_rendered(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $branch = Branch::factory()->create();
        ClubResource::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->get('/reservas/create')
            ->assertOk()
            ->assertSee('Selecione a Data')
            ->assertSee('Horários Disponíveis');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_public_enrollment_page_can_be_rendered(): void
    {
        $branch = Branch::factory()->create();

        $this->get(route('enrollment.create', $branch))
            ->assertOk();
    }
}
