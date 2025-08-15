<?php

namespace App\Models\Saas\Concerns;

use App\Models\Saas\Organization;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Plug-and-play organization membership para Filament 4.
 *
 * Pré-requisitos de banco (MySQL):
 * - Tabela organizations (soft delete recomendado)
 * - Tabela organization_user (pivot) com colunas:
 *   organization_id, user_id, status ENUM('active','suspended','invited') DEFAULT 'active',
 *   is_owner TINYINT(1), is_admin TINYINT(1), role_hint VARCHAR(191) NULL,
 *   panels JSON NULL, deleted_at TIMESTAMP NULL, created_at, updated_at
 *
 * Comportamento:
 * - canAccessPanel(): decide acesso aos painéis do Filament.
 *   - Painel 'saas' (plataforma): liberado por papéis de plataforma.
 *   - Painéis de organização (admin, comercial, financeiro, producao): liberados aqui;
 *     a verificação real ocorre em canAccessOrganization().

 * - getOrganizations(): lista de Organizations às quais o usuário pertence (respeita pivot soft delete).
 * - canAccessOrganization(): exige membership ativo (status='active') e,
 *   se o pivot tiver 'panels' (JSON), o painel atual deve estar permitido.
 *
 * Observações:
 * - Quando o modelo usa o trait `InteractsWithPlatformRoles`, este
 *   trait chama `hasAnyPlatformRole([...])` para liberar o /saas.
 * - Sem o trait de roles, o painel 'saas' não será acessível.
 */
trait HasFilamentTenancy
{
    use InteractsWithOrganizations;

    /** Valores possíveis do status no pivot organization_user */
    public const MEMBERSHIP_ACTIVE = 'active';

    public const MEMBERSHIP_SUSPENDED = 'suspended';

    public const MEMBERSHIP_INVITED = 'invited';

    /**
     * Decide quem pode acessar cada painel do Filament.
     *
     * - 'saas' (plataforma): controlado exclusivamente por `PlatformRole`.
     * - Demais painéis (tenant) são liberados aqui; a decisão fina vai para canAccessTenant().
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        if ($panelId === 'saas') {
            if (method_exists($this, 'hasAnyPlatformRole')) {
                return $this->hasAnyPlatformRole([
                    'platform_owner',
                    'support_agent',
                    'organization_manager',
                    'billing_admin',
                    'readonly',
                ]);
            }

            return false;
        }

        // Para painéis de organização (admin, comercial, financeiro, producao),
        // a checagem realmente restritiva fica em canAccessOrganization().
        return true;
    }

    /**
     * Organizations que o usuário consegue ver/selecionar no painel corrente.
     * Obs: Se quiser filtrar por painel específico, use $panel->getId() e aplique regras.
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->organizations()->get();
    }

    /**
     * Checagem por organização (Organization):
     * - Precisa ser membro (linha na pivot organization_user não deletada).
     * - status precisa ser 'active'.
     * - Se 'panels' (JSON) estiver definido e não for vazio, o painel atual DEVE estar listado.
     * - Ignora qualquer tentativa de liberar 'saas' via pivot->panels (plataforma não é organização).
     */
    public function canAccessTenant(Model $organization): bool
    {
        if (! $organization instanceof Organization) {
            return false;
        }

        // Busca membership já respeitando wherePivotNull('deleted_at')
        $membership = $this->organizations()
            ->whereKey($organization->getKey())
            ->first()?->pivot;

        if (! $membership) {
            return false; // não é membro
        }

        // Precisa estar ATIVO
        if (($membership->status ?? self::MEMBERSHIP_ACTIVE) !== self::MEMBERSHIP_ACTIVE) {
            return false; // suspended ou invited
        }

        // Mini-RBAC por painel (opcional, mas recomendado):
        // Se houver uma lista de painéis no pivot, ela passa a ser deny-by-default.
        $currentPanelId = Filament::getCurrentPanel()?->getId(); // pode ser null em CLI/Jobs
        if ($currentPanelId && ! empty($membership->panels)) {
            // Nunca permita liberar 'saas' por pivot
            if ($currentPanelId === 'saas') {
                return false;
            }

            $allowed = collect($membership->panels ?? []);

            if ($allowed->isNotEmpty() && ! $allowed->contains($currentPanelId)) {
                return false;
            }
        }

        return true;
    }
}
