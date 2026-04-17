<?php

namespace App\Models;

use App\Enums\InventoryMovementReason;
use App\Enums\InventoryMovementType;
use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'branch_id',
        'club_resource_id',
        'reservation_id',
        'actor_id',
        'movement_type',
        'reason',
        'quantity',
        'unit_cost',
        'occurred_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => InventoryMovementType::class,
            'reason' => InventoryMovementReason::class,
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function resource()
    {
        return $this->belongsTo(ClubResource::class, 'club_resource_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
