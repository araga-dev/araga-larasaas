<?php

namespace App\Filament\Saas\Resources\Organizations\Tables;

use App\Filament\Saas\Resources\Organizations\RelationManagers\UsersRelationManager;
use App\Filament\Saas\Resources\Organizations\Schemas\OrganizationUserForm;
use App\Models\Saas\OrganizationUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrganizationUsersTable
{
    public static function configure(Table $table): Table
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
                    ->schema(array_merge([
                        // Select do usuário (o Attach espera "recordId")
                        Select::make('recordId')
                            ->label('Usuário')
                            ->searchable()
                            ->preload()
                            ->options(
                                User::query()
                                    ->orderBy('email')
                                    ->pluck('email', 'id')
                                    ->all()
                            )
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                User::query()
                                    ->where(fn($q) => $q
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%"))
                                    ->limit(50)
                                    ->pluck('email', 'id')
                                    ->all()
                            )
                            ->required(),
                    ], OrganizationUserForm::pivotFields()))
                    ->using(function ($record, array $data, UsersRelationManager $livewire) {
                        $organization = $livewire->getOwnerRecord();
                        $userId  = $data['recordId'];
                        $payload = Arr::except($data, ['recordId']);

                        DB::transaction(function () use ($organization, $userId, $payload) {
                            // É o primeiro membro ativo desta conta?
                            // (A relação users() já filtra deleted_at = null)
                            $isFirstActiveMember = ! $organization->users()->exists();

                            // Se for o primeiro, força owner/admin/active
                            if ($isFirstActiveMember) {
                                $payload['is_owner'] = true;
                                $payload['is_admin'] = $payload['is_admin'] ?? true;
                                $payload['status']   = 'active';
                            }

                            // Procura vínculo existente (incluindo soft-deleted)
                            $existingPivot = $organization->usersWithTrashedPivot()
                                ->where('users.id', $userId)
                                ->first()?->pivot;

                            if ($existingPivot) {
                                // Restaura/atualiza
                                $organization->users()->updateExistingPivot(
                                    $userId,
                                    array_merge($payload, ['deleted_at' => null])
                                );
                            } else {
                                // Cria do zero
                                $organization->users()->attach($userId, $payload);
                            }

                            // Garante owner único se marcou/isFirst
                            if (! empty($payload['is_owner'])) {
                                OrganizationUser::query()
                                    ->where('organization_id', $organization->getKey())
                                    ->where('user_id', '!=', $userId)
                                    ->whereNull('deleted_at')
                                    ->update(['is_owner' => false]);
                            }
                        });
                    })
                    ->visible(
                        fn(UsersRelationManager $livewire): bool =>
                        auth()->user()?->can('update', $livewire->getOwnerRecord()) ?? false
                    ),

                // Transferir propriedade
                Action::make('transferirPropriedade')
                    ->label('Transferir propriedade')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->schema([
                        Select::make('user_id')
                            ->label('Novo proprietário')
                            ->options(
                                fn(UsersRelationManager $livewire) =>
                                $livewire->getOwnerRecord()
                                    ->users()
                                    ->pluck('email', 'users.id')
                            )
                            ->searchable()
                            ->required(),
                        Toggle::make('tambem_admin')
                            ->label('Tornar admin')
                            ->default(true),
                    ])
                    ->action(function (array $data, UsersRelationManager $livewire) {
                        $organization = $livewire->getOwnerRecord();

                        DB::transaction(function () use ($organization, $data) {
                            OrganizationUser::query()
                                ->where('organization_id', $organization->getKey())
                                ->whereNull('deleted_at')
                                ->update(['is_owner' => false]);

                            $organization->users()->updateExistingPivot(
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
                        fn(UsersRelationManager $livewire): bool =>
                        auth()->user()?->can('update', $livewire->getOwnerRecord()) ?? false
                    )
                    ->requiresConfirmation(),
            ])
            ->recordActions([
                EditAction::make(), // edita os campos do pivot

                // Soft detach
                Action::make('remover')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($record, UsersRelationManager $livewire) {
                        if (data_get($record, 'pivot.is_owner', false)) {
                            abort(422, 'Transfira a propriedade antes de remover o proprietário.');
                        }

                        $livewire->getOwnerRecord()
                            ->users()
                            ->updateExistingPivot($record->getKey(), ['deleted_at' => now()]);
                    }),

                // Tornar proprietário
                Action::make('tornarProprietario')
                    ->icon('heroicon-o-user-plus')
                    ->label('Tornar proprietário')
                    ->visible(fn($record) => ! (bool) data_get($record, 'pivot.is_owner', false))
                    ->requiresConfirmation()
                    ->action(function ($record, UsersRelationManager $livewire) {
                        $organization = $livewire->getOwnerRecord();

                        DB::transaction(function () use ($organization, $record) {
                            OrganizationUser::query()
                                ->where('organization_id', $organization->getKey())
                                ->whereNull('deleted_at')
                                ->update(['is_owner' => false]);

                            $organization->users()->updateExistingPivot($record->getKey(), [
                                'is_owner'   => true,
                                'status'     => 'active',
                                'deleted_at' => null,
                            ]);
                        });
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('removerSelecionados')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($records, UsersRelationManager $livewire) {
                        $organization = $livewire->getOwnerRecord();

                        if ($records->first(fn($r) => (bool) data_get($r, 'pivot.is_owner'))) {
                            abort(422, 'Você não pode remover o proprietário. Transfira a propriedade antes.');
                        }

                        foreach ($records as $user) {
                            $organization->users()->updateExistingPivot($user->getKey(), ['deleted_at' => now()]);
                        }
                    }),
            ]);
    }
}
