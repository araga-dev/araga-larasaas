<?php

namespace App\Filament\Account\Resources\Branches\Tables;

use App\Models\Saas\Branch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('code')->label('Código')->toggleable(),
                IconColumn::make('is_active')->label('Ativa')->boolean(),
                TextColumn::make('parent.name')->label('Superior')->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Branch $record) {
                        if ($record->account && $record->account->primary_branch_id === $record->id) {
                            abort(403, 'Não é possível excluir a filial principal.');
                        }
                    }),
                Action::make('definir_principal')
                    ->label('Definir como principal')
                    ->requiresConfirmation()
                    ->action(function (Branch $record) {
                        $account = $record->account;
                        if ($account) {
                            $account->primary_branch_id = $record->id;
                            $account->save();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    #DeleteBulkAction::make(),
                    #ForceDeleteBulkAction::make(),
                    #RestoreBulkAction::make(),
                ]),
            ]);
    }
}
