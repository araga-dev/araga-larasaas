<?php

namespace App\Filament\Saas\Resources\Accounts\Tables;

use App\Filament\Saas\Resources\Accounts\Schemas\AccountUserForm;
use App\Models\Saas\Branch;
use App\Models\Saas\AccountUser;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BrachesTable
{
    public static function configure(Table $table, $account): Table
    {
        return $table
            ->heading('Filiais')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('account_id', $account->id))
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('parent.name')
                    ->label('Pai')
                    ->toggleable(),

                IconColumn::make('is_primary')
                    ->label('Padrão')
                    ->boolean()
                    ->trueIcon('heroicon-m-star')
                    ->falseIcon('heroicon-m-star')
                    ->trueColor('warning'),

                IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->since()  // exibe "há 3 min"
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Ativa'),
                TernaryFilter::make('is_primary')
                    ->label('Padrão'),
                SelectFilter::make('parent_id')
                    ->label('Pai')
                    ->options(
                        Branch::query()
                            ->where('account_id', $account->id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    ),
                TrashedFilter::make(), // se quiser lidar com soft-deletes
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        // normaliza is_primary: evita colisão no UNIQUE (account_id, is_primary)
                        $data['is_primary'] = !empty($data['is_primary']) ? 1 : null;
                        return $data;
                    }),
                DeleteAction::make(),
                ForceDeleteAction::make()->visible(false),
                RestoreAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) use ($account) {
                        // garante a associação correta
                        $data['account_id'] = $account->id;
                        $data['is_primary'] = !empty($data['is_primary']) ? 1 : null;
                        return $data;
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make()->visible(false),
            ])
            ->defaultSort('name');
    }
}
