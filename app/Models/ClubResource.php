<?php

namespace App\Models;

use Database\Factories\ClubResourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubResource extends Model
{
    /** @use HasFactory<ClubResourceFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'type',
        'description',
        'max_capacity',
        'default_price',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedules()
    {
        return $this->hasMany(ResourceSchedule::class);
    }

    public function blocks()
    {
        return $this->hasMany(ResourceBlock::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'club_resource_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'club_resource_id');
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'plan_resource');
    }
}
