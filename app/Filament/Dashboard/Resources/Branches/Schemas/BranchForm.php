<?php

namespace App\Filament\Dashboard\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->helperText('Ex: Matriz, Filial SP'),
                TextInput::make('code')
                    ->label('Código')
                    ->maxLength(50)
                    ->helperText('Código interno opcional'),
                Select::make('parent_id')
                    ->label('Filial superior')
                    ->relationship('parent', 'name')
                    ->nullable()
                    ->helperText('Use para definir hierarquia entre filiais'),
                Toggle::make('is_active')
                    ->label('Ativa')
                    ->default(true)
                    ->helperText('Filiais inativas não estarão disponíveis'),
            ]);
    }
}
