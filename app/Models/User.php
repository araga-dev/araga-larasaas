<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Saas\Concerns\HasFilamentTenancy;
use App\Models\Saas\Concerns\InteractsWithPlatformRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasFilamentTenancy;
    use InteractsWithPlatformRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
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

    public function ownedAccounts(): BelongsToMany
    {
        return $this->accounts()->wherePivot('is_owner', true);
    }

    public function adminAccounts(): BelongsToMany
    {
        return $this->accounts()->wherePivot('is_admin', true);
    }

    public function memberAccounts(): BelongsToMany
    {
        return $this->accounts()
            ->wherePivot('is_owner', false)
            ->wherePivot('is_admin', false);
    }
}
