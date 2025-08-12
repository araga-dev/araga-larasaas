<?php

namespace App\Models\Saas\Concerns;

use App\Models\Saas\Account;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


/**
 * Plug-and-play tenancy para Filament 4.
 *
 * Pré-requisitos de banco (MySQL):
 * - Tabela accounts (soft delete recomendado)
 * - Tabela account_user (pivot) com colunas:
 *   account_id, user_id, status ENUM('active','suspended','invited') DEFAULT 'active',
 *   is_owner TINYINT(1), is_admin TINYINT(1), role_hint VARCHAR(191) NULL,
 *   panels JSON NULL, deleted_at TIMESTAMP NULL, created_at, updated_at
 *
 * Comportamento:
 * - canAccessPanel(): decide acesso aos painéis do Filament.
 *   - Painel 'saas' (plataforma): por papéis de plataforma OU por e-mails em config.
 *   - Painéis tenant (admin, comercial, financeiro, producao): liberados aqui;
 *     a verificação real ocorre em canAccessTenant().
 *
 * - getTenants(): lista de Accounts às quais o usuário pertence (respeita pivot soft delete).
 * - canAccessTenant(): exige membership ativo (status='active') e,
 *   se o pivot tiver 'panels' (JSON), o painel atual deve estar permitido.
 *
 * Observações:
 * - Caso você tenha o trait HasPlatformRoles no User, este trait usará
 *   hasAnyPlatformRole([...]) para liberar o /saas. Se não houver, cai no fallback por config.
 * - Configure config/araga_saas.php com 'platform_owner_emails' quando não usar papéis de plataforma.
 */
trait HasFilamentTenancy
{
    use InteractsWithAccounts;

    /** Valores possíveis do status no pivot account_user */
    public const MEMBERSHIP_ACTIVE   = 'active';
    public const MEMBERSHIP_SUSPENDED = 'suspended';
    public const MEMBERSHIP_INVITED   = 'invited';


    /**
     * Decide quem pode acessar cada painel do Filament.
     *
     * - 'saas' (plataforma): preferencialmente por papéis de plataforma (HasPlatformRoles),
     *   senão por fallback de e-mails na configuração.
     * - Demais painéis (tenant) são liberados aqui; a decisão fina vai para canAccessTenant().
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        if ($panelId === 'saas') {
            // 1) Se o User tiver helpers de papéis de plataforma, use-os:
            if (method_exists($this, 'hasAnyPlatformRole')) {
                // Ajuste a lista conforme seus papéis de plataforma
                return $this->hasAnyPlatformRole([
                    'platform_owner',
                    'support_agent',
                    'account_manager',
                    'billing_admin',
                    'readonly',
                ]);
            }

            // 2) Fallback: e-mails definidos na config/env
            $owners = collect(config('araga_saas.platform_owner_emails', []))
                ->filter()
                ->map(fn($e) => mb_strtolower(trim($e)));

            return $owners->contains(mb_strtolower((string) $this->email));
        }

        // Para painéis tenant-aware (admin, comercial, financeiro, producao),
        // a checagem realmente restritiva fica em canAccessTenant().
        return true;
    }

    /**
     * Tenants (Accounts) que o usuário consegue ver/selecionar no painel corrente.
     * Obs: Se quiser filtrar por painel específico, use $panel->getId() e aplique regras.
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->accounts()->get();
    }

    /**
     * Checagem por tenant (Account):
     * - Precisa ser membro (linha na pivot account_user não deletada).
     * - status precisa ser 'active'.
     * - Se 'panels' (JSON) estiver definido e não for vazio, o painel atual DEVE estar listado.
     * - Ignora qualquer tentativa de liberar 'saas' via pivot->panels (plataforma não é tenant).
     */
    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Account) {
            return false;
        }

        // Busca membership já respeitando wherePivotNull('deleted_at')
        $membership = $this->accounts()
            ->whereKey($tenant->getKey())
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
