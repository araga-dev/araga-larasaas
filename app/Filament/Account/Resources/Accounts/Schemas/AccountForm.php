<?php

namespace App\Filament\Account\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(120)
                    ->helperText('Nome exibido para todos os usuários da conta.'),
                TextInput::make('slug')
                    ->label('Slug')
                    ->disabled()
                    ->maxLength(120),
                TextInput::make('plan')
                    ->label('Plano')
                    ->maxLength(120)
                    ->helperText('Informativo, não editável pelo suporte.'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativa',
                        'suspended' => 'Suspensa',
                    ])
                    ->required()
                    ->helperText('Status operacional da conta.'),
            ]);
    }
}
