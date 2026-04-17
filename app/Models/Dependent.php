<?php

namespace App\Models;

use App\Enums\DependentStatus;
use App\Enums\ProposalOrigin;
use Database\Factories\DependentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependent extends Model
{
    /** @use HasFactory<DependentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_id',
        'branch_id',
        'relationship',
        'status',
        'source',
        'approved_at',
        'approved_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => DependentStatus::class,
            'source' => ProposalOrigin::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function reservations()
    {
        return $this->morphMany(Reservation::class, 'reserver');
    }
}
