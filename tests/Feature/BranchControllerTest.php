<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_matrix_can_view_the_branch_index_page(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $response = $this
            ->actingAs($user)
            ->get('/filiais');

        $response->assertOk();
    }

    public function test_admin_matrix_can_view_the_branch_create_page(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $response = $this
            ->actingAs($user)
            ->get('/filiais/create');

        $response->assertOk();
    }
}
