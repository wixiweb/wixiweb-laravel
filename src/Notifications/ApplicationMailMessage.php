<?php

namespace Wixiweb\WixiwebLaravel\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ApplicationMailMessage extends MailMessage
{

    public function __construct()
    {
        if (config('wixiweb.mail.bcc') !== null) {
            $this->bcc(config('wixiweb.mail.bcc'));
        }
    }
}
