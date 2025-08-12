<?php

namespace App\Filament\Saas\Resources\Accounts\Tables;

use App\Filament\Saas\Resources\Accounts\Schemas\AccountUserForm;
use App\Models\Saas\AccountUser;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AccountUsersTable
{
    public static function configure(Table $table, RelationManager $rm): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                IconColumn::make('pivot.is_owner')->label('Owner')->boolean(),
                IconColumn::make('pivot.is_admin')->label('Admin')->boolean(),
                TextColumn::make('pivot.status')->label('Status')->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'invited',
                        'danger'  => 'suspended',
                    ]),
                TagsColumn::make('pivot.panels')->label('Painéis'),
                TextColumn::make('pivot.updated_at')->label('Atualizado')->since(),
            ])
            ->headerActions([
                // Adicionar usuário
                AttachAction::make()
                    ->label('Adicionar usuário')
                    ->form(array_merge([
                        // Select do usuário (o Attach espera "recordId")
                        Select::make('recordId')
                            ->label('Usuário')
                            ->searchable()
                            ->preload()
                            ->options(
                                \App\Models\User::query()
                                    ->orderBy('email')
                                    ->pluck('email', 'id')
                                    ->all()
                            )
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                \App\Models\User::query()
                                    ->where(fn($q) => $q
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%"))
                                    ->limit(50)
                                    ->pluck('email', 'id')
                                    ->all()
                            )
                            ->required(),
                    ], AccountUserForm::pivotFields()))
                    ->using(function ($record, array $data) use ($rm) {
                        $account = $rm->getOwnerRecord();
                        $userId  = $data['recordId'];
                        $payload = Arr::except($data, ['recordId']);

                        DB::transaction(function () use ($account, $userId, $payload) {
                            // É o primeiro membro ativo desta conta?
                            // (A relação users() já filtra deleted_at = null)
                            $isFirstActiveMember = ! $account->users()->exists();

                            // Se for o primeiro, força owner/admin/active
                            if ($isFirstActiveMember) {
                                $payload['is_owner'] = true;
                                $payload['is_admin'] = $payload['is_admin'] ?? true;
                                $payload['status']   = 'active';
                            }

                            // Procura vínculo existente (incluindo soft-deleted)
                            $existingPivot = $account->usersWithTrashedPivot()
                                ->where('users.id', $userId)
                                ->first()?->pivot;

                            if ($existingPivot) {
                                // Restaura/atualiza
                                $account->users()->updateExistingPivot(
                                    $userId,
                                    array_merge($payload, ['deleted_at' => null])
                                );
                            } else {
                                // Cria do zero
                                $account->users()->attach($userId, $payload);
                            }

                            // Garante owner único se marcou/isFirst
                            if (! empty($payload['is_owner'])) {
                                AccountUser::query()
                                    ->where('account_id', $account->getKey())
                                    ->where('user_id', '!=', $userId)
                                    ->whereNull('deleted_at')
                                    ->update(['is_owner' => false]);
                            }
                        });
                    })
                    ->visible(
                        fn(): bool =>
                        auth()->user()?->can('update', $rm->getOwnerRecord()) ?? false
                    ),

                // Transferir propriedade
                Action::make('transferirPropriedade')
                    ->label('Transferir propriedade')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->form([
                        Select::make('user_id')
                            ->label('Novo proprietário')
                            ->options(
                                fn() =>
                                $rm->getOwnerRecord()
                                    ->users()
                                    ->pluck('email', 'users.id')
                            )
                            ->searchable()
                            ->required(),
                        Toggle::make('tambem_admin')
                            ->label('Tornar admin')
                            ->default(true),
                    ])
                    ->action(function (array $data) use ($rm) {
                        $account = $rm->getOwnerRecord();

                        DB::transaction(function () use ($account, $data) {
                            AccountUser::query()
                                ->where('account_id', $account->getKey())
                                ->whereNull('deleted_at')
                                ->update(['is_owner' => false]);

                            $account->users()->updateExistingPivot(
                                $data['user_id'],
                                [
                                    'is_owner'   => true,
                                    'status'     => 'active',
                                    'is_admin'   => (bool) ($data['tambem_admin'] ?? true),
                                    'deleted_at' => null,
                                ]
                            );
                        });
                    })
                    ->visible(
                        fn(): bool =>
                        auth()->user()?->can('update', $rm->getOwnerRecord()) ?? false
                    )
                    ->requiresConfirmation(),
            ])
            ->actions([
                EditAction::make(), // edita os campos do pivot

                // Soft detach
                Action::make('remover')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($record) use ($rm) {
                        if (data_get($record, 'pivot.is_owner', false)) {
                            abort(422, 'Transfira a propriedade antes de remover o proprietário.');
                        }

                        $rm->getOwnerRecord()
                            ->users()
                            ->updateExistingPivot($record->getKey(), ['deleted_at' => now()]);
                    }),

                // Tornar proprietário
                Action::make('tornarProprietario')
                    ->icon('heroicon-o-user-plus')
                    ->label('Tornar proprietário')
                    ->visible(fn($record) => ! (bool) data_get($record, 'pivot.is_owner', false))
                    ->requiresConfirmation()
                    ->action(function ($record) use ($rm) {
                        $account = $rm->getOwnerRecord();

                        DB::transaction(function () use ($account, $record) {
                            AccountUser::query()
                                ->where('account_id', $account->getKey())
                                ->whereNull('deleted_at')
                                ->update(['is_owner' => false]);

                            $account->users()->updateExistingPivot($record->getKey(), [
                                'is_owner'   => true,
                                'status'     => 'active',
                                'deleted_at' => null,
                            ]);
                        });
                    }),
            ])
            ->bulkActions([
                BulkAction::make('removerSelecionados')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($records) use ($rm) {
                        $account = $rm->getOwnerRecord();

                        if ($records->first(fn($r) => (bool) data_get($r, 'pivot.is_owner'))) {
                            abort(422, 'Você não pode remover o proprietário. Transfira a propriedade antes.');
                        }

                        foreach ($records as $user) {
                            $account->users()->updateExistingPivot($user->getKey(), ['deleted_at' => now()]);
                        }
                    }),
            ]);
    }
}
