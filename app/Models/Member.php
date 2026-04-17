<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\ProposalOrigin;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'primary_branch_id',
        'plan_id',
        'status',
        'source',
        'custom_monthly_fee',
        'approved_at',
        'approved_by_user_id',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'source' => ProposalOrigin::class,
            'custom_monthly_fee' => 'decimal:2',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function primaryBranch()
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function additionalBranches()
    {
        return $this->belongsToMany(Branch::class, 'member_branch_affiliations');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }

    public function invoices()
    {
        return $this->hasMany(MembershipInvoice::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function allBranchIds(): Collection
    {
        return $this->additionalBranches->pluck('id')->push($this->primary_branch_id)->unique()->values();
    }

    public function resolvedMonthlyFee(): float
    {
        return (float) (
            $this->custom_monthly_fee
            ?? $this->primaryBranch?->monthly_fee_default
            ?? $this->plan?->base_price
            ?? 0
        );
    }

    public function freeReservationsUsedInMonth(Carbon $month): int
    {
        return $this->reservations()
            ->whereMonth('reservation_date', $month->month)
            ->whereYear('reservation_date', $month->year)
            ->count();
    }
}
