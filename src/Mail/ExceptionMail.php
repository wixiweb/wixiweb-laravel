<?php

namespace Wixiweb\WixiwebLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;
use Wixiweb\WixiwebLaravel\Exceptions\MailableException;

class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    private MailableException $exception;
    private array $exceptionGlobalContext;

    public function __construct(MailableException $exception, array $exceptionGlobalContext)
    {
        $this->exception = $exception;
        $this->exceptionGlobalContext = $exceptionGlobalContext;
    }

    public function envelope() : Envelope
    {
        $reflect = new ReflectionClass($this->exception);
        return new Envelope(
            subject: '['.$reflect->getShortName().'] '.$this->exception->getMessage(),
        );
    }

    public function content() : Content
    {
        $exceptionContext = (method_exists($this->exception, 'context'))
            ? $this->exception->context()
            : [];
        $context = array_merge($this->exceptionGlobalContext, $exceptionContext);

        return new Content(
            view: 'wixiweb::emails.exception',
            with: [
                'exception' => $this->exception,
                'context' => $context,
            ],
        );
    }
}
