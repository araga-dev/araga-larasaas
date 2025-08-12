<?php

namespace App\Models\Saas\Policies;

use App\Models\User;
use App\Models\Saas\Account;

class AccountPolicy
{
    protected function isPlatformOwner(User $user): bool
    {
        // 1) Por papel de plataforma
        if (method_exists($user, 'hasPlatformRole') && $user->hasPlatformRole('platform_owner')) {
            return true;
        }

        // 2) Fallback por email em config
        $owners = collect(config('araga_saas.platform_owner_emails', []))
            ->filter()->map(fn($e) => mb_strtolower(trim($e)));

        return $owners->contains(mb_strtolower((string) $user->email));
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
