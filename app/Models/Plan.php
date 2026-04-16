<?php

namespace App\Models;

use App\Enums\DiscountType;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price',
        'dependent_limit',
        'guest_limit_per_reservation',
        'free_reservations_per_month',
        'extra_reservation_discount_type',
        'extra_reservation_discount_value',
        'dependents_inherit_benefits',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'extra_reservation_discount_type' => DiscountType::class,
            'extra_reservation_discount_value' => 'decimal:2',
            'dependents_inherit_benefits' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function resources()
    {
        return $this->belongsToMany(ClubResource::class, 'plan_resource');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
