<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
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

    public function test_branch_index_shows_pending_members_summary_instead_of_resources(): void
    {
        $user = User::factory()->adminMatrix()->create();
        $activeBranch = Branch::factory()->create(['is_active' => true]);
        $inactiveBranch = Branch::factory()->create(['is_active' => false]);

        Member::factory()->for($activeBranch, 'primaryBranch')->create(['status' => 'active']);
        Member::factory()->count(2)->for($activeBranch, 'primaryBranch')->create(['status' => 'pending']);
        Member::factory()->for($inactiveBranch, 'primaryBranch')->create(['status' => 'pending']);

        $response = $this
            ->actingAs($user)
            ->get('/filiais');

        $response->assertOk();
        $response->assertViewHas('summary', function (array $summary): bool {
            return $summary['branches'] === 2
                && $summary['active'] === 1
                && $summary['members'] === 4
                && $summary['pending_members'] === 3
                && ! array_key_exists('resources', $summary);
        });
        $response->assertSeeText('Pendencias');
        $response->assertSeeText('3');
    }

    public function test_admin_matrix_can_store_public_branch_settings(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->post(route('filiais.store'), [
                'name' => 'AABB Curitiba',
                'slug' => 'aabb-curitiba',
                'type' => 'branch',
                'email' => 'contato@aabbcuritiba.test',
                'phone' => '(41) 3333-0000',
                'address' => 'Rua Central, 1000',
                'monthly_fee_default' => '189.90',
                'is_active' => '1',
                'settings' => [
                    'city' => 'Curitiba',
                    'summary' => 'Unidade com parque aquatico e agenda social.',
                    'public_phone' => '(41) 3333-1111',
                    'public_whatsapp' => '(41) 99999-2222',
                    'public_hours' => 'Seg a Dom, 08h as 22h',
                ],
            ])
            ->assertRedirect(route('filiais.index'));

        $branch = Branch::query()->where('slug', 'aabb-curitiba')->firstOrFail();

        $this->assertSame('Curitiba', $branch->settings['city']);
        $this->assertSame('Unidade com parque aquatico e agenda social.', $branch->settings['summary']);
        $this->assertSame('4133331111', $branch->settings['public_phone']);
        $this->assertSame('41999992222', $branch->settings['public_whatsapp']);
        $this->assertSame('Seg a Dom, 08h as 22h', $branch->settings['public_hours']);
    }

    public function test_landing_renders_branch_city_and_summary_from_settings(): void
    {
        Branch::factory()->create([
            'name' => 'AABB Recife',
            'slug' => 'recife',
            'settings' => [
                'city' => 'Recife',
                'summary' => 'Estrutura esportiva e social da unidade.',
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Recife')
            ->assertSeeText('Estrutura esportiva e social da unidade.');
    }
}
