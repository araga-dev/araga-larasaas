<?php

namespace  App\Filament\Dashboard\Resources\Accounts\Pages;

use App\Filament\Dashboard\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\ListRecords;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
