<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\UserResource;
use App\Notifications\UserPasswordNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = $data['password'] ?? null;

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();

        if (($state['send_password_email'] ?? false) && $this->plainPassword) {
            $this->record->notify(new UserPasswordNotification($this->plainPassword));
        }
    }
}
