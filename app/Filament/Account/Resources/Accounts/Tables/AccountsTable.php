<?php

namespace App\Filament\Account\Resources\Accounts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Dados da conta')
            ->description('Informações básicas da sua conta.')
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('plan')->label('Plano'),
                TextColumn::make('status')->label('Status'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
