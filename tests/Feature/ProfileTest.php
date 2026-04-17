<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPngUploads;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use CreatesPngUploads;
    use RefreshDatabase;

    public function test_admin_profile_page_is_displayed(): void
    {
        $user = User::factory()->adminMatrix()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_admin_profile_information_can_be_updated(): void
    {
        $user = User::factory()->adminMatrix()->create();

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

    public function test_admin_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->adminMatrix()->create();

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

    public function test_admin_can_delete_their_account(): void
    {
        $user = User::factory()->adminMatrix()->create();

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

    public function test_admin_must_provide_the_correct_password_to_delete_the_account(): void
    {
        $user = User::factory()->adminMatrix()->create();

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

    public function test_member_profile_hides_the_membership_card_and_delete_controls(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Centro']);
        $user = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Maria Silva',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('Dados')
            ->assertSee('Seguranca')
            ->assertSee('id="phone"', false)
            ->assertDontSee('aria-label="Carteirinha digital"', false)
            ->assertDontSee('Excluir conta')
            ->assertDontSee('id="name"', false);
    }

    public function test_dependent_profile_hides_the_membership_card_and_delete_controls(): void
    {
        $branch = Branch::factory()->create(['name' => 'Clube Sul']);
        $holder = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Carlos Titular',
        ]);

        $member = Member::factory()->create([
            'user_id' => $holder->id,
            'primary_branch_id' => $branch->id,
        ]);

        $user = User::factory()->dependent()->create([
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
            ->assertSee('id="phone"', false)
            ->assertDontSee('aria-label="Carteirinha digital"', false)
            ->assertDontSee('Excluir conta')
            ->assertDontSee('id="name"', false);
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

        ClubSetting::query()->updateOrCreate(['id' => 1], ['card_prefix' => 'CS']);

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
        $user = User::factory()->adminMatrix()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'profile_photo' => $this->fakePngUpload('perfil.png', 640, 640),
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

    public function test_member_can_update_email_phone_and_photo_without_changing_the_name(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'role' => UserRole::Member,
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'phone' => '11911112222',
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nome Ignorado',
                'email' => 'maria.nova@example.com',
                'phone' => '11977778888',
                'profile_photo' => $this->fakePngUpload('carteirinha.png', 640, 640),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $freshUser = $user->fresh();

        $this->assertSame('Maria Silva', $freshUser->name);
        $this->assertSame('maria.nova@example.com', $freshUser->email);
        $this->assertSame('11977778888', $freshUser->getRawOriginal('phone'));
        $this->assertNotNull($freshUser->profile_photo_media_asset_id);
    }

    public function test_member_delete_is_forbidden(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'role' => UserRole::Member,
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    public function test_dependent_delete_is_forbidden(): void
    {
        $branch = Branch::factory()->create();
        $holder = User::factory()->create([
            'role' => UserRole::Member,
        ]);

        $member = Member::factory()->create([
            'user_id' => $holder->id,
            'primary_branch_id' => $branch->id,
        ]);

        $user = User::factory()->dependent()->create();

        Dependent::factory()->create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_validation_errors_keep_the_profile_tab_active(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'role' => UserRole::Member,
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
        ]);

        $response = $this
            ->from(route('profile.edit'))
            ->followingRedirects()
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'email' => 'email-invalido',
                'phone' => $user->phone,
            ]);

        $response
            ->assertOk()
            ->assertSee('data-profile-layout="tabbed"', false)
            ->assertSee('data-active-profile-tab="profile"', false);
    }

    public function test_password_validation_errors_keep_the_security_tab_active(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'role' => UserRole::Member,
        ]);

        Member::factory()->create([
            'user_id' => $user->id,
            'primary_branch_id' => $branch->id,
        ]);

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
}
