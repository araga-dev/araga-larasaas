<?php

namespace App\Models\Saas\Policies;

use App\Models\Saas\Account;
use App\Models\User;

class AccountPolicy
{
    /**
     * Verifica se o usuário possui o papel de dono da plataforma.
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

    public function view(User $user, Account $a): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function create(User $user): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function update(User $user, Account $a): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function delete(User $user, Account $a): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function restore(User $user, Account $a): bool
    {
        return $this->isPlatformOwner($user);
    }

    public function forceDelete(User $user, Account $a): bool
    {
        return false;
    }
}
