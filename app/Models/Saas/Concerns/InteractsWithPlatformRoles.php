<?php

namespace App\Models\Saas\Concerns;

use App\Models\Saas\PlatformRole;
use App\Models\Saas\PlatformRoleUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithPlatformRoles
{
    protected ?array $cachedPlatformRoleSlugs = null;

    public function platformRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            PlatformRole::class,      // related
            'platform_role_user',     // pivot table
            'user_id',                // foreign key (this)
            'role_id'                 // related key (role)
        )
            ->using(PlatformRoleUser::class)  // opcional, mas bom se você tiver o pivot model
            ->withPivot(['is_active'])
            ->withTimestamps();
    }

    public function hasPlatformRole(string $slug): bool
    {
        return $this->platformRoles()
            ->where('platform_roles.slug', $slug) // use o nome da tabela para evitar colisão
            ->wherePivot('is_active', 1)
            ->exists();
    }

    public function hasAnyPlatformRole(array $slugs): bool
    {
        if (empty($slugs)) {
            return false;
        }

        return $this->platformRoles()
            ->whereIn('platform_roles.slug', $slugs)
            ->wherePivot('is_active', 1)
            ->exists();
    }

    /**
     * Cache simples de 1 request: evita N queries em múltiplos checks.
     */
    public function cachedActiveRoleSlugs(): array
    {
        if ($this->cachedPlatformRoleSlugs === null) {
            $this->cachedPlatformRoleSlugs = $this->platformRoles()
                ->wherePivot('is_active', 1)
                ->pluck('platform_roles.slug')
                ->all();
        }

        return $this->cachedPlatformRoleSlugs;
    }
}
