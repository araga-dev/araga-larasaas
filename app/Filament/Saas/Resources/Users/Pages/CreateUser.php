<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
