<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'club_resource_id',
        'member_id',
        'reserver_type',
        'reserver_id',
        'reservation_date',
        'start_time',
        'end_time',
        'guest_count',
        'original_amount',
        'charged_amount',
        'status',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'original_amount' => 'decimal:2',
            'charged_amount' => 'decimal:2',
            'status' => ReservationStatus::class,
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function resource()
    {
        return $this->belongsTo(ClubResource::class, 'club_resource_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function reserver()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
