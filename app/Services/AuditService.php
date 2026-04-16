<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(?User $actor, string $event, ?Model $auditable = null, array $payload = [], ?Branch $branch = null): void
    {
        AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'branch_id' => $branch?->id ?? $this->resolveBranchId($auditable),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'payload' => $payload,
        ]);
    }

    protected function resolveBranchId(?Model $auditable): ?int
    {
        if (! $auditable) {
            return null;
        }

        if (isset($auditable->branch_id)) {
            return (int) $auditable->branch_id;
        }

        if (isset($auditable->primary_branch_id)) {
            return (int) $auditable->primary_branch_id;
        }

        return null;
    }
}
