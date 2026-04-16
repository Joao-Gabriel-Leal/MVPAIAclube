<?php

namespace App\Models;

use App\Enums\BranchType;
use App\Support\MaskFormatter;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'monthly_fee_default',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => BranchType::class,
            'monthly_fee_default' => 'decimal:2',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MaskFormatter::phone($value),
            set: fn (?string $value) => MaskFormatter::digits($value),
        );
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class, 'primary_branch_id');
    }

    public function affiliatedMembers()
    {
        return $this->belongsToMany(Member::class, 'member_branch_affiliations');
    }

    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }

    public function resources()
    {
        return $this->hasMany(ClubResource::class);
    }

    public function invoices()
    {
        return $this->hasMany(MembershipInvoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
