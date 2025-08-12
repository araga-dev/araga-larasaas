<?php

namespace App\Filament\Account\Pages\Tenancy;

use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class EditAccountProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Perfil da conta';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome')->required()->maxLength(120),
            TextInput::make('slug')->label('Slug')->disabled(),
            TextInput::make('plan')->label('Plano')->maxLength(120)->nullable(),
            Select::make('status')->options([
                'active' => 'Ativa',
                'suspended' => 'Suspensa',
            ])->required(),
        ]);
    }
}
