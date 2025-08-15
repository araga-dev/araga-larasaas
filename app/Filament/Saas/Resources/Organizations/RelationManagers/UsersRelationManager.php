<?php

namespace App\Filament\Saas\Resources\Organizations\RelationManagers;

use App\Filament\Saas\Resources\Organizations\Schemas\OrganizationUserForm;
use App\Filament\Saas\Resources\Organizations\Tables\OrganizationUsersTable;
use App\Models\Saas\OrganizationUser;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users'; // Organization::users()

    public function form(Schema $schema): Schema
    {
        return OrganizationUserForm::configure($schema);
    }



    public function table(Table $table): Table
    {
        return OrganizationUsersTable::configure($table);
    }


    /**
     * Hook do Relation Manager para mutar/validar dados do PIVOT ANTES de salvar.
     * Se is_owner=true, zera os demais owners desta conta (transferência implícita).
     */
    protected function mutateRelationshipDataBeforeSave(array $data): array
    {
        if (! empty($data['is_owner'])) {
            $organization = $this->getOwnerRecord();

            DB::transaction(function () use ($organization) {
                OrganizationUser::query()
                    ->where('organization_id', $organization->getKey())
                    ->whereNull('deleted_at')
                    ->update(['is_owner' => false]);
            });
        }

        return $data;
    }
}
