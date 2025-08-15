<?php

namespace App\Models\Saas\Concerns;

use App\Models\Saas\Organization;
use App\Models\Saas\OrganizationUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithOrganizations
{
    /**
     * Organizações (organizations) às quais o usuário pertence.
     * Inclui colunas necessárias do pivot e respeita soft delete do pivot via wherePivotNull('deleted_at').
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_user',    // pivot table
            'user_id',         // fk deste model
            'organization_id'       // fk relacionado
        )
            ->using(OrganizationUser::class) // se você criou o pivot model
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

    public function softDetachOrganization(int|Organization $organization): void
    {
        $organizationId = $organization instanceof Organization ? $organization->getKey() : $organization;
        $this->organizations()->updateExistingPivot($organizationId, ['deleted_at' => now()]);
    }

    public function forceDetachOrganization(int|Organization $organization): void
    {
        $organizationId = $organization instanceof Organization ? $organization->getKey() : $organization;
        $this->organizations()->detach($organizationId);
    }

    public function organizationsWithTrashedPivot(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_user',
            'user_id',
            'organization_id'
        )
            ->using(OrganizationUser::class)
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

    public function restoreOrganization(int|Organization $organization): void
    {
        $organizationId = $organization instanceof Organization ? $organization->getKey() : $organization;

        // agora encontra inclusive soft-deleted:
        $updated = $this->organizationsWithTrashedPivot()
            ->updateExistingPivot($organizationId, ['deleted_at' => null]);

        if (! $updated) {
            $this->organizations()->attach($organizationId, ['status' => 'active']);
        }
    }
}
