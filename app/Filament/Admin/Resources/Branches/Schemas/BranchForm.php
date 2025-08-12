<?php

namespace App\Filament\Admin\Resources\Branches\Schemas;

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
                TextInput::make('name')->label('Nome')->required(),
                TextInput::make('code')->label('Código')->maxLength(50),
                Select::make('parent_id')
                    ->label('Filial superior')
                    ->relationship('parent', 'name')
                    ->nullable(),
                Toggle::make('is_active')->label('Ativa')->default(true),
            ]);
    }
}
