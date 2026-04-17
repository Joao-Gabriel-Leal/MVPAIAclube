<?php

namespace Database\Seeders;

use App\Enums\DependentStatus;
use App\Enums\MembershipStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Reservation;
use App\Models\ResourceSchedule;
use App\Models\User;
use App\Services\CardPublicTokenGenerator;
use App\Services\CardSuffixGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClubDemoSeeder extends Seeder
{
    public function run(): void
    {
        $cardSuffixGenerator = app(CardSuffixGenerator::class);
        $cardPublicTokenGenerator = app(CardPublicTokenGenerator::class);

        $branches = Branch::query()->get()->keyBy('slug');
        $plans = Plan::query()->get()->keyBy('slug');

        $resources = collect();

        foreach ($branches as $branch) {
            foreach ([
                ['name' => 'Churrasqueira', 'price' => 150, 'capacity' => 20],
                ['name' => 'Quadra Poliesportiva', 'price' => 90, 'capacity' => 12],
                ['name' => 'Parque Aquatico', 'price' => 180, 'capacity' => 40],
                ['name' => 'Ginasio', 'price' => 260, 'capacity' => 80],
                ['name' => 'Salao de Eventos', 'price' => 320, 'capacity' => 100],
            ] as $item) {
                $resource = ClubResource::query()->updateOrCreate(
                    ['branch_id' => $branch->id, 'slug' => Str::slug($item['name'])],
                    [
                        'name' => $item['name'],
                        'type' => $item['name'],
                        'description' => $item['name'].' da unidade '.$branch->name,
                        'max_capacity' => $item['capacity'],
                        'default_price' => $item['price'],
                        'is_active' => true,
                    ]
                );

                ResourceSchedule::query()->where('club_resource_id', $resource->id)->delete();
                foreach (range(0, 6) as $day) {
                    ResourceSchedule::query()->create([
                        'club_resource_id' => $resource->id,
                        'day_of_week' => $day,
                        'opens_at' => '08:00',
                        'closes_at' => '22:00',
                        'slot_interval_minutes' => 60,
                        'is_active' => true,
                    ]);
                }

                $resources->push($resource);
            }
        }

        $plans['individual']->resources()->sync(
            $resources->filter(fn (ClubResource $resource) => in_array($resource->name, ['Churrasqueira', 'Quadra Poliesportiva'], true))->pluck('id')
        );
        $plans['familia']->resources()->sync($resources->pluck('id'));
        $plans['comunidade']->resources()->sync($resources->pluck('id'));

        $matrixAdmin = User::query()->updateOrCreate(
            ['email' => 'admin.matriz@clube.test'],
            [
                'name' => 'Gestor Clube AABB',
                'role' => UserRole::AdminMatrix,
                'phone' => '(61) 99999-0001',
                'branch_id' => $branches['clube-aabb']->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin.brasilia@clube.test'],
            [
                'name' => 'Gestor AABB Brasilia',
                'role' => UserRole::AdminBranch,
                'phone' => '(61) 99999-0002',
                'branch_id' => $branches['brasilia']->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin.saopaulo@clube.test'],
            [
                'name' => 'Gestor AABB Sao Paulo',
                'role' => UserRole::AdminBranch,
                'phone' => '(11) 99999-0003',
                'branch_id' => $branches['sao-paulo']->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $memberUser = User::query()->updateOrCreate(
            ['email' => 'associado@clube.test'],
            [
                'name' => 'Carlos Pereira',
                'role' => UserRole::Member,
                'cpf' => '12345678901',
                'birth_date' => '1988-04-12',
                'phone' => '(11) 98888-0001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $member = Member::query()->updateOrCreate(
            ['user_id' => $memberUser->id],
            [
                'primary_branch_id' => $branches['brasilia']->id,
                'plan_id' => $plans['familia']->id,
                'status' => MembershipStatus::Active,
                'approved_at' => now(),
                'approved_by_user_id' => $matrixAdmin->id,
            ]
        );
        $member->additionalBranches()->sync([$branches['sao-paulo']->id, $branches['sao-luis']->id]);

        $pendingMemberUser = User::query()->updateOrCreate(
            ['email' => 'pendente@clube.test'],
            [
                'name' => 'Marina Costa',
                'role' => UserRole::Member,
                'cpf' => '12345678902',
                'birth_date' => '1992-10-10',
                'phone' => '(11) 98888-0002',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Member::query()->updateOrCreate(
            ['user_id' => $pendingMemberUser->id],
            [
                'primary_branch_id' => $branches['salvador']->id,
                'plan_id' => $plans['individual']->id,
                'status' => MembershipStatus::Pending,
            ]
        );

        $delinquentUser = User::query()->updateOrCreate(
            ['email' => 'inadimplente@clube.test'],
            [
                'name' => 'Roberto Lima',
                'role' => UserRole::Member,
                'cpf' => '12345678903',
                'birth_date' => '1980-05-20',
                'phone' => '(11) 98888-0003',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $delinquentMember = Member::query()->updateOrCreate(
            ['user_id' => $delinquentUser->id],
            [
                'primary_branch_id' => $branches['rio-de-janeiro']->id,
                'plan_id' => $plans['comunidade']->id,
                'status' => MembershipStatus::Delinquent,
                'approved_at' => now()->subMonths(6),
                'approved_by_user_id' => $matrixAdmin->id,
            ]
        );

        $dependentUser = User::query()->updateOrCreate(
            ['email' => 'dependente@clube.test'],
            [
                'name' => 'Ana Pereira',
                'role' => UserRole::Dependent,
                'cpf' => '12345678904',
                'birth_date' => '2010-08-25',
                'phone' => '(11) 97777-0004',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Dependent::query()->updateOrCreate(
            ['user_id' => $dependentUser->id],
            [
                'member_id' => $member->id,
                'branch_id' => $member->primary_branch_id,
                'relationship' => 'Filha',
                'status' => DependentStatus::Active,
                'approved_at' => now(),
                'approved_by_user_id' => $matrixAdmin->id,
            ]
        );

        $pendingDependentUser = User::query()->updateOrCreate(
            ['email' => 'dependente.pendente@clube.test'],
            [
                'name' => 'Pedro Pereira',
                'role' => UserRole::Dependent,
                'cpf' => '12345678905',
                'birth_date' => '2014-01-15',
                'phone' => '(11) 97777-0005',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        Dependent::query()->updateOrCreate(
            ['user_id' => $pendingDependentUser->id],
            [
                'member_id' => $member->id,
                'branch_id' => $member->primary_branch_id,
                'relationship' => 'Filho',
                'status' => DependentStatus::Pending,
            ]
        );

        $resource = ClubResource::query()
            ->where('branch_id', $branches['brasilia']->id)
            ->where('slug', 'churrasqueira')
            ->first();

        Reservation::query()->updateOrCreate(
            [
                'club_resource_id' => $resource->id,
                'member_id' => $member->id,
                'reservation_date' => now()->addDays(5)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '12:00',
            ],
            [
                'branch_id' => $resource->branch_id,
                'reserver_type' => $member->getMorphClass(),
                'reserver_id' => $member->id,
                'guest_count' => 2,
                'original_amount' => 150,
                'charged_amount' => 0,
                'status' => ReservationStatus::Confirmed,
                'created_by_user_id' => $matrixAdmin->id,
            ]
        );

        Reservation::query()->updateOrCreate(
            [
                'club_resource_id' => $resource->id,
                'member_id' => $delinquentMember->id,
                'reservation_date' => now()->addDays(7)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '16:00',
            ],
            [
                'branch_id' => $resource->branch_id,
                'reserver_type' => $delinquentMember->getMorphClass(),
                'reserver_id' => $delinquentMember->id,
                'guest_count' => 4,
                'original_amount' => 150,
                'charged_amount' => 97.5,
                'status' => ReservationStatus::Confirmed,
                'created_by_user_id' => $matrixAdmin->id,
            ]
        );

        User::query()
            ->whereIn('role', [UserRole::Member, UserRole::Dependent])
            ->where(function ($query) {
                $query->whereNull('card_suffix')
                    ->orWhereNull('card_public_token');
            })
            ->orderBy('id')
            ->get()
            ->each(function (User $user) use ($cardSuffixGenerator, $cardPublicTokenGenerator) {
                $updates = [];

                if (! $user->card_suffix) {
                    $updates['card_suffix'] = $cardSuffixGenerator->generate();
                }

                if (! $user->card_public_token) {
                    $updates['card_public_token'] = $cardPublicTokenGenerator->generate();
                }

                if ($updates !== []) {
                    $user->forceFill($updates)->save();
                }
            });
    }
}
