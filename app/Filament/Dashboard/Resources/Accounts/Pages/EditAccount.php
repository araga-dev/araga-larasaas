<?php

namespace App\Filament\Dashboard\Resources\Accounts\Pages;

use App\Filament\Dashboard\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\EditRecord;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
