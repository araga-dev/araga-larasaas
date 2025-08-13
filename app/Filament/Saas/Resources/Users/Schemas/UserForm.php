<?php

namespace App\Filament\Saas\Resources\Users\Schemas;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('Verificado em'),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->rule('confirmed')
                    ->dehydrated(fn ($state) => filled($state))
                    ->suffixAction(
                        Action::make('generate')
                            ->icon('heroicon-m-bolt')
                            ->action(fn (Set $set) => $set('password', Str::random(12)))
                    ),
                TextInput::make('password_confirmation')
                    ->label('Confirmar senha')
                    ->password()
                    ->dehydrated(false)
                    ->required(fn (Get $get) => filled($get('password'))),
                Toggle::make('send_password_email')
                    ->label('Enviar senha por e-mail')
                    ->default(true)
                    ->dehydrated(false),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }
}
