<?php

namespace App\Filament\Account\Resources\Accounts;

use App\Filament\Account\Resources\Accounts\Pages\EditAccount;
use App\Filament\Account\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Account\Resources\Accounts\RelationManagers\UsersRelationManager;
use App\Filament\Account\Resources\Accounts\Schemas\AccountForm;
use App\Filament\Account\Resources\Accounts\Tables\AccountsTable;
use App\Models\Saas\Account;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Minha Conta';
    protected static ?string $label = 'Minha Conta';
    protected static ?string $navigationPluralLabel = 'Minha Conta';
    protected static ?string $pluralLabel = 'Minha Conta';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getTenant() !== null;
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();
        return parent::getEloquentQuery()->whereKey(optional($tenant)->getKey());
    }
}
