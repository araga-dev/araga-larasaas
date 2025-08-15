<?php

namespace App\Filament\Account\Resources\Accounts\RelationManagers;

use App\Filament\Account\Resources\Accounts\Schemas\AccountUserForm;
use App\Filament\Account\Resources\Accounts\Tables\AccountUsersTable;
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
