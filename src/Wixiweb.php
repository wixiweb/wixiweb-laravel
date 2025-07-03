<?php

namespace Wixiweb\WixiwebLaravel;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Wixiweb\WixiwebLaravel\Exceptions\MailableException;
use Wixiweb\WixiwebLaravel\Mail\ExceptionMail;

class Wixiweb
{
    public static function configureExceptionHandler(Exceptions $exceptions): void
    {
        $exceptions->dontReportDuplicates();
        $exceptions->dontTruncateRequestExceptions();

        $mailableExceptionClassesCollection = new Collection(config('wixiweb.logging.mail.exceptions', []));
        $allAreThrowables = $mailableExceptionClassesCollection->every(function (mixed $item) {
            return is_string($item)
                && (
                    (class_exists($item) && is_subclass_of($item, \Throwable::class))
                    || interface_exists($item)
                );
        });

        if ($allAreThrowables === false) {
            throw new \InvalidArgumentException('All mailable exception classes must be a subclass of \Throwable');
        }

        $exceptions->report(function (MailableException $exception,) : void {
            self::sendExceptionMail($exception);
        });

        $exceptions->report(function (Throwable $exception,) use ($mailableExceptionClassesCollection): void {
            $send = false;
            foreach ($mailableExceptionClassesCollection as $mailableExceptionClass) {
                if ($exception instanceof $mailableExceptionClass) {
                    $send = true;
                    break;
                }
            }

            if ($send === false) {
                return;
            }

            self::sendExceptionMail($exception);
        });
    }

    public static function sendExceptionMail(Throwable|MailableException $exception) : void
    {
        $logMailRecipients = config('wixiweb.logging.mail.recipients');
        if (count($logMailRecipients) >= 1) {
            Mail::to($logMailRecipients)
                ->send(
                    new ExceptionMail($exception, Context::all()),
                );
        }
    }
}
