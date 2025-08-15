<?php

namespace App\Filament\Dashboard\Resources\Branches;

use App\Filament\Dashboard\Resources\Branches\Pages\CreateBranch;
use App\Filament\Dashboard\Resources\Branches\Pages\EditBranch;
use App\Filament\Dashboard\Resources\Branches\Pages\ListBranches;
use App\Filament\Dashboard\Resources\Branches\Schemas\BranchForm;
use App\Filament\Dashboard\Resources\Branches\Tables\BranchesTable;
use App\Models\Saas\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Filiais';
    protected static ?string $label = 'Filial';
    protected static ?string $navigationPluralLabel = 'Filiais';
    protected static ?string $pluralLabel = 'Filiais';

    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
