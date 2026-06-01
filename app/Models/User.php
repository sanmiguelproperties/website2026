<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public const AGENT_ROLE_NAMES = ['agente', 'agent'];

    /**
     * For Spatie\Permission: allow roles for both 'web' and 'api' guards.
     */
    // protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image_id',
        'is_active',
        'color_theme_id',

        // Perfil de agente (nullable; se usa cuando el user tiene rol `agente`)
        'agency_id',
        'agent_phone',
        'agent_public_email',
        'agent_bio',
        'agent_profile_media_asset_id',
        'easybroker_agent_id',
        'easybroker_agent_payload',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the profile image for the user.
     */
    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'profile_image_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function agentProfileImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'agent_profile_media_asset_id');
    }

    public function agentProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'agent_user_id');
    }

    public function mlsAgent(): HasOne
    {
        return $this->hasOne(MLSAgent::class, 'user_id');
    }

    public function scopeWithAgentRole(Builder $query): Builder
    {
        return $query->withRoleNames(self::AGENT_ROLE_NAMES);
    }

    public function scopeWithRoleNames(Builder $query, iterable $names): Builder
    {
        $normalizedNames = RoleName::normalizeMany($names);

        if ($normalizedNames === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('roles', fn (Builder $roles) => $roles
            ->whereIn(DB::raw('LOWER(TRIM(name))'), $normalizedNames));
    }

    public function hasRoleNamed(string $name): bool
    {
        $normalizedName = RoleName::normalize($name);

        if ($normalizedName === '') {
            return false;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn ($role) => RoleName::normalize($role->name) === $normalizedName
            );
        }

        return $this->roles()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->exists();
    }

    public function hasAgentRole(): bool
    {
        return collect(self::AGENT_ROLE_NAMES)->contains(
            fn (string $roleName) => $this->hasRoleNamed($roleName)
        );
    }

    /**
     * Get the color theme for the user.
     */
    public function colorTheme(): BelongsTo
    {
        return $this->belongsTo(ColorTheme::class, 'color_theme_id');
    }

    public function corporateEmailAccounts(): HasMany
    {
        return $this->hasMany(CorporateEmailAccount::class);
    }

    public function corporateEmailMessages(): HasMany
    {
        return $this->hasMany(CorporateEmailMessage::class);
    }
}
