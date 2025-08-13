<?php

namespace App\Filament\Saas\Resources\Users\Pages;

use App\Filament\Saas\Resources\Users\UserResource;
use App\Notifications\UserPasswordNotification;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $this->plainPassword = $data['password'];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();

        if ($this->plainPassword && ($state['send_password_email'] ?? false)) {
            $this->record->notify(new UserPasswordNotification($this->plainPassword));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
