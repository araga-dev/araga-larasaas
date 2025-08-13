<?php

namespace App\Filament\Saas\Resources\Accounts;

use App\Filament\Saas\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Saas\Resources\Accounts\Pages\EditAccount;
use App\Filament\Saas\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Saas\Resources\Accounts\RelationManagers\BranchesRelationManager;
use App\Filament\Saas\Resources\Accounts\RelationManagers\UsersRelationManager;
use App\Filament\Saas\Resources\Accounts\Schemas\AccountForm;
use App\Filament\Saas\Resources\Accounts\Tables\AccountsTable;
use App\Models\Saas\Account;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Contas';
    protected static ?string $label = 'Conta';
    protected static ?string $navigationPluralLabel = 'Contas';
    protected static ?string $pluralLabel = 'Contas';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static ?int $navigationSort = 2;


    /** Só registra no menu se o usuário puder ver (Policy->viewAny) */
    public static function shouldRegisterNavigation(): bool
    {

        /**
         * @var \App\Models\User|null
         */
        $auth = auth();
        $user = $auth->user();
        return $user ? $user->can('viewAny', Account::class) : false;
    }


    public static function form(Schema $schema): Schema
    {
        return AccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountsTable::configure($table);
    }



    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            BranchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }
}
