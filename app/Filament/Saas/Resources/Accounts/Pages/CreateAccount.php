<?php

namespace App\Filament\Saas\Resources\Accounts\Pages;

use App\Filament\Saas\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] ??= Str::slug($data['name']);
        return $data;
    }
}
