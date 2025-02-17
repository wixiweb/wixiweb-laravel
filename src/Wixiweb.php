<?php

namespace Wixiweb\WixiwebLaravel;

use DateTimeInterface;
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

        $exceptions->context(fn() => [
            'auth' => [
                'is_authenticated' => request()?->user() !== null,
                'id' => request()?->user()?->id,
            ],
            'now' => now()->format(DateTimeInterface::ATOM),
            'env' => app()->environment(),
            'url' => (app()->runningInConsole()) ? 'CLI' : request()?->fullUrl(),
            'GET' => request()?->query->all() ?? [],
            'POST' => request()?->request->all() ?? [],
            'FILES' => request()?->files->all() ?? [],
        ]);

        $exceptions->report(function (MailableException $exception) {
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
