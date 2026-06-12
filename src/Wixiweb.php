<?php

namespace Wixiweb\WixiwebLaravel;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Wixiweb\WixiwebLaravel\Exceptions\MailableException;
use Wixiweb\WixiwebLaravel\Logging\ContextFilterInterface;
use Wixiweb\WixiwebLaravel\Logging\RedactSensitiveFields;
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
                    new ExceptionMail($exception, self::getFilteredContext()),
                );
        }
    }

    /**
     * Construit le pipeline de filtres : la redaction des champs sensibles
     * (toujours en premier), suivie des filtres custom déclarés en config.
     *
     * @return array<int, ContextFilterInterface>
     */
    public static function buildFilterPipeline(): array
    {
        $hiddenFields = config('wixiweb.logging.context.hidden_fields', []);

        return [
            new RedactSensitiveFields($hiddenFields),
            ...array_map(
                fn (string $class): ContextFilterInterface => new $class,
                config('wixiweb.logging.context.filters', []),
            ),
        ];
    }

    /**
     * Retourne le contexte global après application du pipeline de filtres.
     *
     * @return array<string, mixed>
     */
    public static function getFilteredContext(): array
    {
        return array_reduce(
            self::buildFilterPipeline(),
            fn (array $ctx, ContextFilterInterface $filter): array => $filter->filter($ctx),
            Context::all(),
        );
    }
}
