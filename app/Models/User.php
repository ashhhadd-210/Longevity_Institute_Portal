<?php

namespace App\Models;

use App\Enums\AccountState;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property AccountState $account_state
 * @property Carbon|null $two_factor_confirmed_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Permission names resolved from role grants, cached per request.
     *
     * @var Collection<int, string>|null
     */
    protected ?Collection $resolvedPermissions = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_state' => AccountState::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function fullName(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter()
            ->implode(' ');
    }

    /** @return HasMany<RoleAssignment, $this> */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_assignments')
            ->withPivot(['assigned_by', 'assigned_at']);
    }

    /** @return HasOne<Collaborator, $this> */
    public function collaborator(): HasOne
    {
        return $this->hasOne(Collaborator::class);
    }

    /** @return HasMany<Consent, $this> */
    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    /**
     * Authorization contract: application code checks permissions, never role
     * names (ADR-004). Role names are configuration; permissions are contract.
     */
    public function hasPermission(string $permission): bool
    {
        $this->resolvedPermissions ??= $this->roles()
            ->with('permissions:id,name')
            ->get()
            ->flatMap(fn (Role $role): Collection => $role->permissions->pluck('name'))
            ->unique()
            ->values();

        return $this->resolvedPermissions->contains($permission);
    }

    public function hasRole(string $name): bool
    {
        return $this->roles()->where('name', $name)->exists();
    }

    /**
     * MFA is mandatory for any user holding a role flagged requires_mfa; the
     * admin route group enforces enrollment before access (doc 05 §3).
     */
    public function requiresMfa(): bool
    {
        return $this->roles()->where('requires_mfa', true)->exists();
    }

    public function hasConfirmedMfa(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }
}
