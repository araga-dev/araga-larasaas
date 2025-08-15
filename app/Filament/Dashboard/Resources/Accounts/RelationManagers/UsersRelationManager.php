<?php

namespace  App\Filament\Dashboard\Resources\Accounts\RelationManagers;

use App\Filament\Dashboard\Resources\Accounts\Schemas\AccountUserForm;
use App\Filament\Dashboard\Resources\Accounts\Tables\AccountUsersTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Schema $schema): Schema
    {
        return AccountUserForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return AccountUsersTable::configure($table, $this);
    }
}
