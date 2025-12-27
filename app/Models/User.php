<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Agency;
use App\Models\ColorTheme;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

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
        'color_theme_id',

        // Perfil de agente (nullable; se usa cuando el user tiene rol `agent`)
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

    /**
     * Get the color theme for the user.
     */
    public function colorTheme(): BelongsTo
    {
        return $this->belongsTo(ColorTheme::class, 'color_theme_id');
    }
}
