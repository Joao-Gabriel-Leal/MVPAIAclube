<?php

namespace App\Models;

use App\Enums\DependentStatus;
use App\Enums\MembershipStatus;
use App\Enums\UserRole;
use App\Support\MaskFormatter;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'cpf',
        'birth_date',
        'phone',
        'card_suffix',
        'card_public_token',
        'profile_photo_path',
        'branch_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'role' => UserRole::class,
            'password' => 'hashed',
        ];
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MaskFormatter::cpf($value),
            set: fn (?string $value) => MaskFormatter::digits($value),
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => MaskFormatter::phone($value),
            set: fn (?string $value) => MaskFormatter::digits($value),
        );
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function member()
    {
        return $this->hasOne(Member::class);
    }

    public function dependent()
    {
        return $this->hasOne(Dependent::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function isAdminMatrix(): bool
    {
        return $this->role === UserRole::AdminMatrix;
    }

    public function isAdminBranch(): bool
    {
        return $this->role === UserRole::AdminBranch;
    }

    public function isMember(): bool
    {
        return $this->role === UserRole::Member;
    }

    public function isDependent(): bool
    {
        return $this->role === UserRole::Dependent;
    }

    public function managedBranchIds(): Collection
    {
        if ($this->isAdminMatrix()) {
            return Branch::query()->pluck('id');
        }

        if ($this->isAdminBranch() && $this->branch_id) {
            return collect([$this->branch_id]);
        }

        if ($this->isMember() && $this->member) {
            return $this->member->allBranchIds();
        }

        if ($this->isDependent() && $this->dependent) {
            return collect([$this->dependent->branch_id]);
        }

        return collect();
    }

    public function activeMember(): ?Member
    {
        if ($this->isMember()) {
            return $this->member;
        }

        return $this->dependent?->member;
    }

    public function isCardHolder(): bool
    {
        return $this->isMember() || $this->isDependent();
    }

    public function getFormattedCardNumberAttribute(): ?string
    {
        if (! $this->isCardHolder() || ! $this->card_suffix) {
            return null;
        }

        return ClubSetting::current()->card_prefix.'-'.Str::upper($this->card_suffix);
    }

    public function getCardValidationUrlAttribute(): ?string
    {
        if (! $this->isCardHolder() || ! $this->card_public_token) {
            return null;
        }

        $relativePath = URL::route('cards.show', $this->card_public_token, false);
        $publicBaseUrl = $this->resolveCardPublicBaseUrl();

        if ($publicBaseUrl) {
            return $publicBaseUrl.$relativePath;
        }

        return URL::route('cards.show', $this->card_public_token);
    }

    public function getCardStatusLabelAttribute(): ?string
    {
        if ($this->isMember()) {
            return $this->member?->status?->label();
        }

        if ($this->isDependent()) {
            return $this->dependent?->status?->label();
        }

        return null;
    }

    public function getCardStatusToneAttribute(): string
    {
        if ($this->isMember()) {
            return match ($this->member?->status) {
                MembershipStatus::Active => 'success',
                MembershipStatus::Pending => 'warning',
                MembershipStatus::Cancelled, MembershipStatus::Delinquent => 'danger',
                default => 'info',
            };
        }

        if ($this->isDependent()) {
            return match ($this->dependent?->status) {
                DependentStatus::Active => 'success',
                DependentStatus::Pending => 'warning',
                DependentStatus::Cancelled => 'danger',
                default => 'info',
            };
        }

        return 'info';
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function getProfileInitialsAttribute(): string
    {
        $initials = collect(preg_split('/\s+/', trim($this->name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $segment) => Str::upper(Str::substr($segment, 0, 1)))
            ->join('');

        return $initials !== '' ? $initials : 'CL';
    }

    protected function resolveCardPublicBaseUrl(): ?string
    {
        $configuredBaseUrl = trim((string) config('app.card_public_base_url'));

        if ($configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/');
        }

        if (! app()->bound('request')) {
            return null;
        }

        $host = request()->getHost();

        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1', '[::1]'], true)) {
            return null;
        }

        return rtrim(request()->getSchemeAndHttpHost(), '/');
    }
}
