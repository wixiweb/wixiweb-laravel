<?php

namespace Wixiweb\WixiwebLaravel\Notifications;

use Illuminate\Notifications\Notification;

trait ConfigurableNotifiable
{
    /**
     * @see https://laravel.com/docs/11.x/notifications#customizing-the-recipient
     * @param Notification $notification
     * @return array|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        return config('wixiweb.mail.to') ?? $this->email ?? '';
    }
}
