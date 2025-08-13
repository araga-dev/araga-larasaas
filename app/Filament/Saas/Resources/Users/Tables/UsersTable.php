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
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['ownedAccounts', 'adminAccounts', 'memberAccounts']))
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                ToggleColumn::make('is_active')->label('Ativo')->sortable(),
                TextColumn::make('email_verified_at')->label('Verificado em')->dateTime()->sortable(),
                TextColumn::make('created_at')->label('Criado em')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Atualizado em')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('accounts')
                    ->label('Contas')
                    ->formatStateUsing(fn($state, $record) => $record->accounts->map(fn($acc) => $acc->name)->join(', ')),
                TextColumn::make('account_roles')
                    ->label('Papéis nas contas')
                    ->formatStateUsing(fn($state, $record) => $record->accounts->map(function ($acc) {
                        $roles = [];
                        if ($acc->pivot->is_owner) $roles[] = 'Dono';
                        if ($acc->pivot->is_admin) $roles[] = 'Admin';
                        if (!$acc->pivot->is_owner && !$acc->pivot->is_admin) $roles[] = 'Usuário';
                        return $acc->name . ' (' . implode('/', $roles) . ')';
                    })->join(', ')),
                TextColumn::make('account_status')
                    ->label('Status nas contas')
                    ->formatStateUsing(fn($state, $record) => $record->accounts->map(fn($acc) => $acc->pivot->status)->unique()->join(', ')),
                TextColumn::make('platform_roles')
                    ->label('Papéis de plataforma')
                    ->formatStateUsing(fn($state, $record) => $record->platformRoles->map(fn($role) => $role->name)->join(', ')),
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
                            'owner' => $query->whereHas('accounts', fn(Builder $q) => $q->wherePivot('is_owner', true)),
                            'admin' => $query->whereHas('accounts', fn(Builder $q) => $q->wherePivot('is_admin', true)),
                            'user' => $query->whereHas('accounts', fn(Builder $q) => $q->wherePivot('is_owner', false)->wherePivot('is_admin', false)),
                            default => $query,
                        };
                    }),
                /*  SelectFilter::make('account_status')
                    ->label('Status nas contas')
                    ->options([
                        'active' => 'Ativo',
                        'suspended' => 'Suspenso',
                        'invited' => 'Convidado',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'] ?? null;
                        return $query->whereHas('accounts', function (Builder $q) use ($status) {
                            $q->where('account_user.status', $status);
                        });
                    }),*/
                SelectFilter::make('platform_roles')
                    ->label('Papel de plataforma')
                    ->relationship('platformRoles', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('email_verified_at')
                    ->label('E-mail verificado')
                    ->trueLabel('Verificado')
                    ->falseLabel('Não verificado'),
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
