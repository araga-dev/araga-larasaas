<?php

namespace App\Filament\Saas\Resources\Accounts\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(120)
                    ->unique(ignorable: fn($record) => $record),
                TextInput::make('plan')->label('Plano')->maxLength(120),
                Select::make('status')->options([
                    'active' => 'Ativa',
                    'suspended' => 'Suspensa',
                ])->required(),
            ]);
    }
}
