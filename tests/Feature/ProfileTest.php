<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPngUploads;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use CreatesPngUploads;
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }

    public function test_member_profile_displays_the_membership_card(): void
    {
        config([
            'app.card_public_base_url' => 'http://127.0.0.1:8000',
        ]);

        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'card_prefix' => 'CS',
        ]);
        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $plan = Plan::factory()->create(['name' => 'Plano Ouro']);
        $user = User::factory()->create([
            'role' => UserRole::Member,
            'card_suffix' => 'AB12CD',
            'card_public_token' => 'memberprofiletoken000001',
            'cpf' => '12345678901',
            'name' => 'Maria Silva',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('Dados')
            ->assertSee('Seguranca')
            ->assertSee('Conta')
            ->assertSee('Carteirinha digital')
            ->assertSee('Frente digital')
            ->assertSee('Verso digital')
            ->assertSee('CS-AB12CD')
            ->assertSee('Plano Ouro')
            ->assertSee('Clube Centro')
            ->assertSee('http://127.0.0.1:8000/carteirinhas/memberprofiletoken000001');
    }

    public function test_dependent_profile_displays_the_membership_card(): void
    {
        config([
            'app.card_public_base_url' => 'http://127.0.0.1:8000',
        ]);

        ClubSetting::query()->updateOrCreate([
            'id' => 1,
        ], [
            'card_prefix' => 'CS',
        ]);
        $branch = Branch::factory()->create(['name' => 'Clube Sul']);
        $plan = Plan::factory()->create(['name' => 'Plano Familia']);
        $holder = User::factory()->create([
            'role' => UserRole::Member,
            'card_suffix' => 'ZX34YU',
            'name' => 'Carlos Titular',
        ]);

        $member = Member::factory()->create([
            'user_id' => $holder->id,
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
        ]);

        $user = User::factory()->dependent()->create([
            'card_suffix' => 'QW12ER',
            'card_public_token' => 'dependentprofiletoken001',
            'cpf' => '98765432100',
            'name' => 'Ana Dependente',
        ]);

        Dependent::factory()->create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'branch_id' => $branch->id,
            'relationship' => 'Filha',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('CS-QW12ER')
            ->assertSee('Carlos Titular')
            ->assertSee('Filha')
            ->assertSee('Clube Sul')
            ->assertSee('http://127.0.0.1:8000/carteirinhas/dependentprofiletoken001');
    }

    public function test_admin_profile_does_not_display_the_membership_card(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('data-profile-layout="tabbed"', false)
            ->assertDontSee('role="tablist"', false)
            ->assertDontSee('Carteirinha digital');
    }

    public function test_membership_card_uses_the_configured_public_base_url(): void
    {
        config([
            'app.url' => 'http://127.0.0.1:8000',
            'app.card_public_base_url' => 'https://cards.clubehub.test/',
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Member,
            'card_suffix' => 'AB12CD',
            'card_public_token' => 'publicbaseurltoken00001',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertSame(
            'https://cards.clubehub.test/carteirinhas/publicbaseurltoken00001',
            $user->card_validation_url
        );
    }

    public function test_profile_photo_can_be_uploaded(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'profile_photo' => $this->fakePngUpload('carteirinha.png', 640, 640),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $freshUser = $user->fresh();

        $this->assertNotNull($freshUser->profile_photo_media_asset_id);
        $this->assertDatabaseHas('media_assets', [
            'id' => $freshUser->profile_photo_media_asset_id,
            'context' => 'profile_photo',
            'visibility' => 'private',
        ]);

        $this->actingAs($freshUser)
            ->get($freshUser->profile_photo_url)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_profile_validation_errors_keep_the_profile_tab_active(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from(route('profile.edit'))
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'email-invalido',
            ]);

        $response
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('data-active-profile-tab="profile"', false);
    }

    public function test_password_validation_errors_keep_the_security_tab_active(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from(route('profile.edit'))
            ->followingRedirects()
            ->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'senha-incorreta',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('data-active-profile-tab="security"', false);
    }

    public function test_delete_account_validation_errors_keep_the_account_tab_active(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from(route('profile.edit'))
            ->followingRedirects()
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'senha-incorreta',
            ]);

        $response
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('data-active-profile-tab="account"', false);
    }
}
