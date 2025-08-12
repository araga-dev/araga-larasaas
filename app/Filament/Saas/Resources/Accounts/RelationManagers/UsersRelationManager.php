<?php

namespace App\Filament\Saas\Resources\Accounts\RelationManagers;

use App\Filament\Saas\Resources\Accounts\Schemas\AccountUserForm;
use App\Filament\Saas\Resources\Accounts\Tables\AccountUsersTable;
use App\Models\Saas\AccountUser;
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
    protected static string $relationship = 'users'; // Account::users()

    public function form(Schema $schema): Schema
    {
        return AccountUserForm::configure($schema);
    }



    public function table(Table $table): Table
    {
        return AccountUsersTable::configure($table, $this);
    }


    /**
     * Hook do Relation Manager para mutar/validar dados do PIVOT ANTES de salvar.
     * Se is_owner=true, zera os demais owners desta conta (transferência implícita).
     */
    protected function mutateRelationshipDataBeforeSave(array $data): array
    {
        if (! empty($data['is_owner'])) {
            $account = $this->getOwnerRecord();

            DB::transaction(function () use ($account) {
                AccountUser::query()
                    ->where('account_id', $account->getKey())
                    ->whereNull('deleted_at')
                    ->update(['is_owner' => false]);
            });
        }

        return $data;
    }
}
