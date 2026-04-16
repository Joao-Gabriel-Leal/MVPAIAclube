<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Plan;

class EnrollmentService
{
    public function __construct(
        protected MemberService $memberService,
        protected AuditService $auditService,
    ) {
    }

    public function enroll(Branch $branch, Plan $plan, array $data): Member
    {
        $member = $this->memberService->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'],
            'birth_date' => $data['birth_date'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'primary_branch_id' => $branch->id,
            'plan_id' => $plan->id,
            'status' => MembershipStatus::Pending->value,
            'additional_branch_ids' => [],
        ]);

        $this->auditService->log(null, 'member.enrolled_publicly', $member, [
            'branch' => $branch->slug,
            'plan' => $plan->slug,
        ], $branch);

        return $member;
    }
}
