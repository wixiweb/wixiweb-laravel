<?php

namespace Wixiweb\WixiwebLaravel;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;
use Wixiweb\WixiwebLaravel\Exceptions\MailableException;
use Wixiweb\WixiwebLaravel\Mail\ExceptionMail;

class Wixiweb
{
    public static function configureExceptionHandler(Exceptions $exceptions) : void
    {
        $exceptions->dontReportDuplicates();
        $exceptions->dontTruncateRequestExceptions();

        $exceptions->report(function (MailableException $exception) : void {
            $logMailRecipients = config('wixiweb.logging.mail.recipients');
            if (count($logMailRecipients) >= 1) {
                Mail::to($logMailRecipients)
                    ->send(
                        new ExceptionMail($exception, Context::all())
                    );
            }
        });
    }
}
