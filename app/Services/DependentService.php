<?php

namespace App\Services;

use App\Enums\DependentStatus;
use App\Enums\ProposalOrigin;
use App\Enums\UserRole;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DependentService
{
    public function __construct(
        protected AuditService $auditService,
        protected CardSuffixGenerator $cardSuffixGenerator,
        protected CardPublicTokenGenerator $cardPublicTokenGenerator,
    ) {
    }

    public function create(array $data, User $actor): Dependent
    {
        $member = Member::query()->with(['plan', 'dependents'])->findOrFail($data['member_id']);
        $this->assertPlanLimit($member);

        return DB::transaction(function () use ($data, $actor, $member) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => UserRole::Dependent,
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'phone' => $data['phone'],
                'card_suffix' => $this->cardSuffixGenerator->generate(),
                'card_public_token' => $this->cardPublicTokenGenerator->generate(),
                'password' => Hash::make($data['password']),
            ]);

            $dependent = Dependent::query()->create([
                'user_id' => $user->id,
                'member_id' => $member->id,
                'branch_id' => $data['branch_id'] ?? $member->primary_branch_id,
                'relationship' => $data['relationship'],
                'status' => DependentStatus::Pending,
                'source' => ProposalOrigin::Manual->value,
            ]);

            $this->auditService->log($actor, 'dependent.created', $dependent);

            return $dependent->load(['user', 'member.user', 'branch']);
        });
    }

    public function update(Dependent $dependent, array $data, User $actor): Dependent
    {
        return DB::transaction(function () use ($dependent, $data, $actor) {
            $dependent->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'cpf' => $data['cpf'],
                'birth_date' => $data['birth_date'],
                'phone' => $data['phone'],
                'password' => filled($data['password'] ?? null)
                    ? Hash::make($data['password'])
                    : $dependent->user->password,
            ]);

            $dependent->update([
                'member_id' => $data['member_id'],
                'branch_id' => $data['branch_id'] ?? $dependent->member->primary_branch_id,
                'relationship' => $data['relationship'],
            ]);

            $this->auditService->log($actor, 'dependent.updated', $dependent);

            return $dependent->load(['user', 'member.user', 'branch']);
        });
    }

    public function updateStatus(Dependent $dependent, DependentStatus $status, User $actor): Dependent
    {
        $dependent->update([
            'status' => $status,
            'approved_at' => $status === DependentStatus::Active ? ($dependent->approved_at ?? now()) : $dependent->approved_at,
            'approved_by_user_id' => $status === DependentStatus::Active ? ($dependent->approved_by_user_id ?? $actor->id) : $dependent->approved_by_user_id,
        ]);

        $this->auditService->log($actor, 'dependent.status_updated', $dependent, [
            'status' => $status->value,
        ]);

        return $dependent->refresh();
    }

    protected function assertPlanLimit(Member $member): void
    {
        $plan = $member->plan;
        $currentCount = $member->dependents()->where('status', '!=', DependentStatus::Cancelled)->count();

        if ($plan && $currentCount >= $plan->dependent_limit) {
            throw new RuntimeException('O plano atual atingiu o limite de dependentes.');
        }
    }
}
