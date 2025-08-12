<?php

namespace App\Models\Saas\Concerns;

use App\Models\Saas\Account;
use App\Models\Saas\AccountUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithAccounts
{
    /**
     * Contas (accounts) às quais o usuário pertence.
     * Inclui colunas necessárias do pivot e respeita soft delete do pivot via wherePivotNull('deleted_at').
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(
            Account::class,
            'account_user',    // pivot table
            'user_id',         // fk deste model
            'account_id'       // fk relacionado
        )
            ->using(AccountUser::class) // se você criou o pivot model
            ->withPivot([
                'is_owner',
                'is_admin',
                'role_hint',
                'status',
                'panels',
                'deleted_at',
            ])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function softDetachAccount(int|Account $account): void
    {
        $accountId = $account instanceof Account ? $account->getKey() : $account;
        $this->accounts()->updateExistingPivot($accountId, ['deleted_at' => now()]);
    }

    public function forceDetachAccount(int|Account $account): void
    {
        $accountId = $account instanceof Account ? $account->getKey() : $account;
        $this->accounts()->detach($accountId);
    }

    public function accountsWithTrashedPivot(): BelongsToMany
    {
        return $this->belongsToMany(
            Account::class,
            'account_user',
            'user_id',
            'account_id'
        )
            ->using(AccountUser::class)
            ->withPivot([
                'is_owner',
                'is_admin',
                'role_hint',
                'status',
                'panels',
                'deleted_at',
            ])
            ->withTimestamps(); // sem wherePivotNull
    }

    public function restoreAccount(int|Account $account): void
    {
        $accountId = $account instanceof Account ? $account->getKey() : $account;

        // agora encontra inclusive soft-deleted:
        $updated = $this->accountsWithTrashedPivot()
            ->updateExistingPivot($accountId, ['deleted_at' => null]);

        if (! $updated) {
            $this->accounts()->attach($accountId, ['status' => 'active']);
        }
    }
}
