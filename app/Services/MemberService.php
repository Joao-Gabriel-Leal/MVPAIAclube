<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Enums\ProposalOrigin;
use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    public function __construct(
        protected AuditService $auditService,
        protected CardSuffixGenerator $cardSuffixGenerator,
        protected CardPublicTokenGenerator $cardPublicTokenGenerator,
    ) {
    }

    public function create(array $data, ?User $actor = null): Member
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => UserRole::Member,
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'phone' => $data['phone'],
                'card_suffix' => $this->cardSuffixGenerator->generate(),
                'card_public_token' => $this->cardPublicTokenGenerator->generate(),
                'password' => Hash::make($data['password']),
            ]);

            $status = $data['status'] ?? MembershipStatus::Pending->value;

            $member = Member::query()->create([
                'user_id' => $user->id,
                'primary_branch_id' => $data['primary_branch_id'],
                'plan_id' => $data['plan_id'],
                'status' => $status,
                'source' => $data['source'] ?? ProposalOrigin::Manual->value,
                'custom_monthly_fee' => $data['custom_monthly_fee'] ?? null,
                'notes' => $data['notes'] ?? null,
                'approved_at' => $status === MembershipStatus::Active->value ? now() : null,
                'approved_by_user_id' => $status === MembershipStatus::Active->value ? $actor?->id : null,
            ]);

            $member->additionalBranches()->sync(
                collect($data['additional_branch_ids'] ?? [])
                    ->reject(fn ($branchId) => (int) $branchId === (int) $data['primary_branch_id'])
                    ->values()
                    ->all()
            );

            $this->auditService->log($actor, 'member.created', $member, [
                'status' => $member->status->value,
            ]);

            return $member->load(['user', 'plan', 'primaryBranch', 'additionalBranches']);
        });
    }

    public function update(Member $member, array $data, User $actor): Member
    {
        return DB::transaction(function () use ($member, $data, $actor) {
            $member->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'phone' => $data['phone'],
                'password' => filled($data['password'] ?? null)
                    ? Hash::make($data['password'])
                    : $member->user->password,
            ]);

            $member->update([
                'primary_branch_id' => $data['primary_branch_id'],
                'plan_id' => $data['plan_id'],
                'custom_monthly_fee' => $data['custom_monthly_fee'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $member->additionalBranches()->sync(
                collect($data['additional_branch_ids'] ?? [])
                    ->reject(fn ($branchId) => (int) $branchId === (int) $data['primary_branch_id'])
                    ->values()
                    ->all()
            );

            $this->auditService->log($actor, 'member.updated', $member);

            return $member->load(['user', 'plan', 'primaryBranch', 'additionalBranches']);
        });
    }

    public function updateStatus(Member $member, MembershipStatus $status, User $actor, ?string $notes = null): Member
    {
        $member->update([
            'status' => $status,
            'approved_at' => $status === MembershipStatus::Active ? ($member->approved_at ?? now()) : $member->approved_at,
            'approved_by_user_id' => $status === MembershipStatus::Active ? ($member->approved_by_user_id ?? $actor->id) : $member->approved_by_user_id,
            'cancelled_at' => $status === MembershipStatus::Cancelled ? now() : null,
            'notes' => filled($notes) ? trim(($member->notes ? $member->notes.PHP_EOL : '').$notes) : $member->notes,
        ]);

        $this->auditService->log($actor, 'member.status_updated', $member, [
            'status' => $status->value,
        ]);

        return $member->refresh();
    }

    public function pendingForUser(User $user)
    {
        $query = Member::query()
            ->with(['user', 'plan', 'primaryBranch'])
            ->where('status', MembershipStatus::Pending);

        if ($user->isAdminBranch()) {
            $query->where('primary_branch_id', $user->branch_id);
        }

        return $query->latest()->get();
    }
}
