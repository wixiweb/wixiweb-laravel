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

        $exceptions->context(function () {
            $context = [
                'auth' => null,
                'now' => now()->format(DateTimeInterface::ATOM),
                'env' => config('app.env'),
                'url' => 'CLI',
            ];

            if (!app()->runningInConsole()) {
                $context['auth'] = [
                    'is_authenticated' => request()?->user() !== null,
                    'id' => request()?->user()?->id,
                ];
                $context['url'] = request()?->fullUrl();
                $context['GET'] = request()?->query->all() ?? [];
                $context['POST'] = request()?->request->all() ?? [];
                $context['FILES'] = request()?->files->all() ?? [];
            }

            return $context;
        });

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
