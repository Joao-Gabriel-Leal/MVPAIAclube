<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use App\Services\CardPublicTokenGenerator;
use App\Services\CardSuffixGenerator;
use App\Services\DependentService;
use App\Services\MemberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardSuffixGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_service_generates_a_card_suffix_on_create(): void
    {
        $branch = Branch::factory()->create();
        $plan = Plan::factory()->create();
        $actor = User::factory()->adminMatrix()->create();

        $member = app(MemberService::class)->create([
            'name' => 'Associado Teste',
            'email' => 'associado.teste@clube.test',
            'cpf' => '12345678901',
            'birth_date' => '1990-01-10',
            'phone' => '11999999999',
            'password' => 'password123',
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ], $actor);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', (string) $member->user->card_suffix);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{24}$/', (string) $member->user->card_public_token);
    }

    public function test_dependent_service_generates_a_card_suffix_on_create(): void
    {
        $branch = Branch::factory()->create();
        $plan = Plan::factory()->create();
        $actor = User::factory()->adminMatrix()->create();
        $holderUser = User::factory()->create([
            'role' => UserRole::Member,
            'card_suffix' => 'MEM123',
        ]);
        $member = Member::factory()->create([
            'user_id' => $holderUser->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        $dependent = app(DependentService::class)->create([
            'member_id' => $member->id,
            'branch_id' => $branch->id,
            'relationship' => 'Filho',
            'name' => 'Dependente Teste',
            'email' => 'dependente.teste@clube.test',
            'cpf' => '10987654321',
            'birth_date' => '2012-06-05',
            'phone' => '11988888888',
            'password' => 'password123',
        ], $actor);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', (string) $dependent->user->card_suffix);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{24}$/', (string) $dependent->user->card_public_token);
    }

    public function test_card_suffix_generator_skips_existing_suffixes(): void
    {
        User::factory()->create([
            'card_suffix' => 'ABC123',
        ]);

        $generator = app(CardSuffixGenerator::class);
        $attempts = ['ABC123', 'XYZ789'];

        $generated = $generator->generate(function () use (&$attempts) {
            return array_shift($attempts);
        });

        $this->assertSame('XYZ789', $generated);
    }

    public function test_card_public_token_generator_returns_a_unique_token(): void
    {
        User::factory()->create([
            'card_public_token' => 'abcdefghijklmnopqrstuvwx',
        ]);

        $generated = app(CardPublicTokenGenerator::class)->generate();

        $this->assertMatchesRegularExpression('/^[a-z0-9]{24}$/', $generated);
        $this->assertNotSame('abcdefghijklmnopqrstuvwx', $generated);
    }
}
