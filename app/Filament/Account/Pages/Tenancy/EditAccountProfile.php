<?php

namespace App\Filament\Account\Pages\Tenancy;

use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EditAccountProfile extends EditTenantProfile
{
    protected static ?string $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'Dados da Conta';
    protected static string|\UnitEnum|null $navigationGroup = 'Plataforma';
    protected static ?int $navigationSort = 0;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Dados da conta';
    }

    public function getSubheading(): ?string
    {
        return 'Atualize as informações básicas da sua conta.';
    }

    public static function getLabel(): string
    {
        return 'Perfil da conta';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(120)
                ->helperText('Nome exibido para todos os usuários da conta.'),
            TextInput::make('slug')
                ->label('Slug')
                ->disabled(),
            TextInput::make('plan')
                ->label('Plano')
                ->maxLength(120)
                ->nullable()
                ->helperText('Campo informativo do plano contratado.'),
            Select::make('status')
                ->options([
                    'active' => 'Ativa',
                    'suspended' => 'Suspensa',
                ])
                ->required()
                ->helperText('Status operacional da conta.'),
        ]);
    }
}
