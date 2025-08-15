<?php

namespace App\Filament\Saas\Resources\Organizations\Pages;

use App\Filament\Saas\Resources\Organizations\OrganizationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] ??= Str::slug($data['name']);
        return $data;
    }
}
