<?php

namespace App\Models;

use App\Enums\BranchType;
use App\Support\MaskFormatter;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function publicCity(): string
    {
        $city = trim((string) $this->settingValue('city'));

        if ($city !== '') {
            return $city;
        }

        $fallback = trim((string) Str::afterLast($this->name, 'AABB '));

        return $fallback !== '' ? $fallback : $this->name;
    }

    public function publicSummary(): string
    {
        return trim((string) $this->settingValue('summary'));
    }

    public function publicPhone(): ?string
    {
        $phone = MaskFormatter::phone((string) $this->settingValue('public_phone'));

        return $phone ?: $this->phone;
    }

    public function publicPhoneLink(): ?string
    {
        $digits = MaskFormatter::digits((string) $this->settingValue('public_phone'))
            ?: MaskFormatter::digits($this->getRawOriginal('phone'));

        return $digits ? 'tel:'.$digits : null;
    }

    public function publicWhatsapp(): ?string
    {
        return MaskFormatter::phone((string) $this->settingValue('public_whatsapp'));
    }

    public function publicWhatsappLink(): ?string
    {
        $digits = MaskFormatter::digits((string) $this->settingValue('public_whatsapp'));

        if (! $digits) {
            return null;
        }

        return 'https://wa.me/'.(Str::startsWith($digits, '55') ? $digits : '55'.$digits);
    }

    public function publicHours(): ?string
    {
        $hours = trim((string) $this->settingValue('public_hours'));

        return $hours !== '' ? $hours : null;
    }

    protected function settingValue(string $key): mixed
    {
        return data_get($this->settings ?? [], $key);
    }
}
