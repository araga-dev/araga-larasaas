<?php

namespace App\Filament\Saas\Resources\Organizations\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrganizationUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Usuário')
                    ->relationship('users', 'email')
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->required()
                    ->visible(fn(Get $get) => blank($get('id'))),

                Toggle::make('is_owner')->label('Owner')->inline(false),
                Toggle::make('is_admin')->label('Admin')->inline(false),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active'    => 'Ativo',
                        'suspended' => 'Suspenso',
                        'invited'   => 'Convidado',
                    ])
                    ->default('active')
                    ->required(),

                TextInput::make('role_hint')->label('Hint de papel')->maxLength(191),

                CheckboxList::make('panels')
                    ->label('Painéis permitidos')
                    ->options(function () {
                        return collect(Filament::getPanels())
                            ->reject(fn($p) => $p->getId() === 'saas')
                            ->mapWithKeys(fn($p) => [$p->getId() => Str::headline($p->getId())])
                            ->all();
                    })
                    ->columns(2)
                    ->helperText('Se vazio, acesso liberado a todos os painéis tenant. ("saas" nunca é liberado).'),
            ]);
    }

    /** Campos do PIVOT (para edição). No attach usaremos estes mesmos campos. */
    public static function pivotFields(): array
    {
        return [
            Toggle::make('is_owner')->label('Owner')->inline(false),
            Toggle::make('is_admin')->label('Admin')->inline(false),

            Select::make('status')
                ->label('Status')
                ->options([
                    'active'    => 'Ativo',
                    'suspended' => 'Suspenso',
                    'invited'   => 'Convidado',
                ])->default('active')->required(),

            TextInput::make('role_hint')->label('Hint de papel')->maxLength(191),

            CheckboxList::make('panels')
                ->label('Painéis permitidos')
                ->options(function () {
                    return collect(Filament::getPanels())
                        ->reject(fn($p) => $p->getId() === 'saas')
                        ->mapWithKeys(fn($p) => [$p->getId() => Str::headline($p->getId())])
                        ->all();
                })
                ->columns(2)
                ->helperText('Se vazio, libera todos os painéis tenant (nunca o "saas").'),
        ];
    }
}
