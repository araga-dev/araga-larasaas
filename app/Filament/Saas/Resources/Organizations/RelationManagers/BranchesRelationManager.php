<?php

namespace App\Filament\Saas\Resources\Organizations\RelationManagers;

use App\Filament\Saas\Resources\Organizations\Schemas\BrancheForm;
use App\Filament\Saas\Resources\Organizations\Tables\BrachesTable;
use App\Models\Saas\Branch;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';
    protected static ?string $title = 'Filiais/Branches';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        /** @var \App\Models\Saas\Organization $organization */
        $organization = $this->getOwnerRecord();
        $that = $this;

        return BrancheForm::configure($schema);
    }


    public function table(Table $table): Table
    {
        /** @var \App\Models\Saas\Organization $organization */
        $organization = $this->getOwnerRecord();
        return BrachesTable::configure($table, $organization);
    }
}
