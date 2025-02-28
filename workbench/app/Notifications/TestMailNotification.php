<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Wixiweb\WixiwebLaravel\Notifications\ApplicationMailMessage;

class TestMailNotification extends Notification
{
    public function __construct()
    {
    }

    public function via($notifiable,): array
    {
        return ['mail'];
    }

    public function toMail($notifiable,): MailMessage
    {
        return (new ApplicationMailMessage())
            ->line('');
    }
}
