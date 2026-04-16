<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::Member,
            'cpf' => fake()->unique()->numerify('###########'),
            'birth_date' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'phone' => fake()->numerify('(##) #####-####'),
            'card_suffix' => null,
            'card_public_token' => null,
            'profile_photo_path' => null,
            'profile_photo_media_asset_id' => null,
            'branch_id' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    public function adminMatrix(?Branch $branch = null): static
    {
        return $this->state(fn () => [
            'role' => UserRole::AdminMatrix,
            'branch_id' => $branch?->id,
            'cpf' => null,
            'birth_date' => null,
        ]);
    }

    public function adminBranch(?Branch $branch = null): static
    {
        return $this->state(fn () => [
            'role' => UserRole::AdminBranch,
            'branch_id' => $branch?->id ?? Branch::factory(),
            'cpf' => null,
            'birth_date' => null,
        ]);
    }

    public function dependent(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Dependent,
        ]);
    }
}
