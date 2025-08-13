<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private string $password)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bem-vindo ao SaaS')
            ->line('Sua conta foi criada no sistema.')
            ->line('Email: '.$notifiable->email)
            ->line('Senha: '.$this->password);
    }
}
