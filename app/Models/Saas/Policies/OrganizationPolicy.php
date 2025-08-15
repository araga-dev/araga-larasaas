<?php

namespace App\Models\Saas\Policies;

use App\Models\Saas\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Verifica se o usuário possui o `PlatformRole` "platform_owner".
     */
    protected function isPlatformOwner(User $user): bool
    {
        return method_exists($user, 'hasPlatformRole')
            && $user->hasPlatformRole('platform_owner');
    }

    public function viewAny(User $user): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function view(User $user, Organization $organization): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function create(User $user): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function restore(User $user, Organization $organization): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function forceDelete(User $user, Organization $organization): bool
    {
        return false;
    }
}
