<?php

namespace App\Filament\Saas\Resources\Accounts\Pages;

use App\Filament\Saas\Resources\Accounts\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
