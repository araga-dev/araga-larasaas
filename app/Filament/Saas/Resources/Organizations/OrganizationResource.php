<?php

namespace App\Filament\Saas\Resources\Organizations;


use App\Filament\Saas\Resources\Organizations\Pages\CreateOrganization;
use App\Filament\Saas\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Saas\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Saas\Resources\Organizations\RelationManagers\BranchesRelationManager;
use App\Filament\Saas\Resources\Organizations\RelationManagers\UsersRelationManager;
use App\Filament\Saas\Resources\Organizations\Schemas\OrganizationForm;
use App\Filament\Saas\Resources\Organizations\Tables\OrganizationsTable;
use App\Models\Saas\Organization;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Organizações';
    protected static ?string $label = 'Organização';
    protected static ?string $navigationPluralLabel = 'Organizações';
    protected static ?string $pluralLabel = 'Organizações';

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
        return $user ? $user->can('viewAny', Organization::class) : false;
    }


    public static function form(Schema $schema): Schema
    {
        return OrganizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationsTable::configure($table);
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
            'index' => ListOrganizations::route('/'),
            'create' => CreateOrganization::route('/create'),
            'edit' => EditOrganization::route('/{record}/edit'),
        ];
    }
}
