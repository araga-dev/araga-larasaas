<?php

namespace App\Filament\Account\Resources\Accounts\Tables;

use App\Filament\Account\Resources\Accounts\Schemas\AccountUserForm;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountUsersTable
{
    public static function configure(Table $table, RelationManager $rm): Table
    {
        return $table
            ->heading('Usuários da conta')
            ->description('Convide ou cadastre usuários para esta conta.')
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
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
            ->filters([])
            ->headerActions([
                Action::make('adicionar')
                    ->label('Adicionar usuário')
                    ->form([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->helperText('Informe o email do usuário a ser convidado.'),
                        TextInput::make('name')
                            ->label('Nome')
                            ->helperText('Necessário apenas para novo cadastro.'),
                        Toggle::make('register')
                            ->label('Cadastrar usuário se não existir')
                            ->default(false),
                    ])
                    ->action(function (array $data) use ($rm) {
                        $account = $rm->getOwnerRecord();
                        $user = User::where('email', $data['email'])->first();

                        if (! $user) {
                            if (! empty($data['register'])) {
                                $user = User::create([
                                    'name' => $data['name'] ?: $data['email'],
                                    'email' => $data['email'],
                                    'password' => bcrypt(Str::random(12)),
                                ]);
                                $status = 'active';
                            } else {
                                $user = User::create([
                                    'name' => $data['name'] ?: $data['email'],
                                    'email' => $data['email'],
                                    'password' => bcrypt(Str::random(12)),
                                ]);
                                $status = 'invited';
                            }
                        } else {
                            $status = 'invited';
                        }

                        DB::transaction(function () use ($account, $user, $status) {
                            $existing = $account->usersWithTrashedPivot()
                                ->where('users.id', $user->id)
                                ->first()?->pivot;

                            $payload = ['status' => $status, 'deleted_at' => null];

                            if ($existing) {
                                $account->users()->updateExistingPivot($user->id, $payload);
                            } else {
                                $account->users()->attach($user->id, $payload);
                            }
                        });
                    }),
            ])
            ->actions([
                EditAction::make(),
                Action::make('remover')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(function ($record) use ($rm) {
                        if (data_get($record, 'pivot.is_owner', false)) {
                            abort(422, 'Não é possível remover o proprietário.');
                        }
                        $rm->getOwnerRecord()
                            ->users()
                            ->updateExistingPivot($record->getKey(), ['deleted_at' => now()]);
                    }),
            ])
            ->bulkActions([]);
    }
}
