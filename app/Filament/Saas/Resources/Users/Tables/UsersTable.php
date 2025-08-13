<?php

namespace App\Filament\Saas\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['ownedAccounts', 'adminAccounts', 'memberAccounts']))
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                ToggleColumn::make('is_active')->label('Ativo')->sortable(),
                TextColumn::make('ownedAccounts.name')
                    ->label('Dono de')
                    ->listWithLineBreaks()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('adminAccounts.name')
                    ->label('Admin de')
                    ->listWithLineBreaks()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('memberAccounts.name')
                    ->label('Usuário de')
                    ->listWithLineBreaks()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')->label('Verificado em')->dateTime()->sortable(),
                TextColumn::make('created_at')->label('Criado em')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Atualizado em')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->trueLabel('Ativo')
                    ->falseLabel('Inativo'),
                SelectFilter::make('accounts')
                    ->label('Conta')
                    ->relationship('accounts', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('account_role')
                    ->label('Tipo de associação')
                    ->options([
                        'owner' => 'Dono',
                        'admin' => 'Admin',
                        'user' => 'Usuário',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'owner' => $query->whereHas('accounts', fn (Builder $q) => $q->wherePivot('is_owner', true)),
                            'admin' => $query->whereHas('accounts', fn (Builder $q) => $q->wherePivot('is_admin', true)),
                            'user' => $query->whereHas('accounts', fn (Builder $q) => $q->wherePivot('is_owner', false)->wherePivot('is_admin', false)),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
