<?php

namespace App\Filament\Saas\Resources\Organizations\Schemas;

use App\Filament\Saas\Resources\Organizations\RelationManagers\BranchesRelationManager;
use App\Models\Saas\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class BrancheForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados da filial')
                ->columns(12)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(191)
                        ->columnSpan(6),

                    TextInput::make('code')
                        ->label('Código')
                        ->helperText('Único por conta. Útil para integrações e relatórios.')
                        ->maxLength(50)
                        ->rule(function (BranchesRelationManager $livewire) {
                            $organizationId = $livewire->getOwnerRecord()->id;
                            $currentId = $livewire->getMountedTableActionRecord()?->id;

                            return Rule::unique('branches', 'code')
                                ->where('organization_id', $organizationId)
                                ->ignore($currentId);
                        })
                        ->columnSpan(6),

                    Select::make('parent_id')
                        ->label('Filial pai (opcional)')
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->options(function (BranchesRelationManager $livewire) {
                            $organizationId = $livewire->getOwnerRecord()->id;
                            $currentId = $livewire->getMountedTableActionRecord()?->id;

                            return Branch::query()
                                ->where('organization_id', $organizationId)
                                ->when($currentId, fn($q) => $q->whereKeyNot($currentId))
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->helperText('Use para organizar a hierarquia (ex.: Região → Cidade).')
                        ->columnSpan(6),

                    Toggle::make('is_active')
                        ->label('Ativa')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(3),

                    Toggle::make('is_primary')
                        ->label('Marcar como padrão da conta')
                        ->helperText('Opcional. Mantemos isso “solto”: apenas um 1 por conta.')
                        ->inline(false)
                        ->columnSpan(3)
                        ->dehydrateStateUsing(fn($state) => $state ? 1 : null),
                ])
                ->columnSpanFull(),

            Grid::make()->schema([
                TextInput::make('organization_id')
                    ->default(fn(BranchesRelationManager $livewire) => $livewire->getOwnerRecord()->id)
                    ->dehydrated()
                    ->hidden(),
            ]),
        ]);
    }
}
