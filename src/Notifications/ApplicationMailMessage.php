<?php

namespace Wixiweb\WixiwebLaravel\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Symfony\Component\Mime\Message;

class ApplicationMailMessage extends MailMessage
{

    public function __construct()
    {
        if (config('wixiweb.mail.bcc') !== null) {
            $this->bcc(config('wixiweb.mail.bcc'));
        }

        if(count(config('wixiweb.mail.tags')) > 0)
        {
            $this->withSymfonyMessage(function(Message $message) {
                $message->getHeaders()->addTextHeader('X-Tags', implode(',',config('wixiweb.mail.tags')));
            });
        }
    }


}
