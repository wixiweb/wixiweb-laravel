<?php

namespace Wixiweb\WixiwebLaravel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;
use Throwable;

class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        readonly public Throwable $exception,
        readonly public array $exceptionGlobalContext
    )
    {
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

    protected function additionalMessageData(): array
    {
        return array_merge(parent::additionalMessageData(), ['is_wixiweb_exception_mail' => true]);
    }


}
