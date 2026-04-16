<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $member = Member::factory();

        return [
            'branch_id' => Branch::factory(),
            'club_resource_id' => ClubResource::factory(),
            'member_id' => $member,
            'reserver_type' => Member::class,
            'reserver_id' => $member,
            'reservation_date' => fake()->dateTimeBetween('now', '+1 month'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'guest_count' => fake()->numberBetween(0, 4),
            'original_amount' => fake()->randomFloat(2, 0, 200),
            'charged_amount' => fake()->randomFloat(2, 0, 200),
            'status' => ReservationStatus::Confirmed,
            'notes' => fake()->optional()->sentence(),
            'created_by_user_id' => User::factory(),
        ];
    }
}
