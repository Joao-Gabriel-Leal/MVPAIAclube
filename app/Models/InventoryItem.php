<?php

namespace App\Models;

use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'club_resource_id',
        'name',
        'category',
        'unit',
        'current_quantity',
        'minimum_quantity',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'decimal:2',
            'minimum_quantity' => 'decimal:2',
            'is_active' => 'boolean',
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

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return (float) $this->current_quantity <= (float) $this->minimum_quantity;
    }
}
