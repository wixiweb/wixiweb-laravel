<?php

namespace Wixiweb\WixiwebLaravel\Auth\Access;

class Response extends \Illuminate\Auth\Access\Response
{
    protected ?string $humanReadableMessage = null;

    public function __construct($allowed, $message = '', $code = null, ?string $humanReadableMessage = null)
    {
        $this->humanReadableMessage = $humanReadableMessage;
        parent::__construct($allowed, $message, $code);
    }

    public function getHumanReadableMessage(): string
    {
        return $this->humanReadableMessage ?? $this->message ?? '';
    }

    public static function allow($message = null, $code = null, $humanReadableMessage = null) : self
    {
        return new self(
            allowed: true,
            message: $message,
            code: $code,
            humanReadableMessage: $humanReadableMessage
        );
    }


    public static function deny($message = null, $code = null, $humanReadableMessage = null) : self
    {
        return new self(
            allowed: false,
            message: $message,
            code: $code,
            humanReadableMessage: $humanReadableMessage
        );
    }

    public static function denyWithStatus($status, $message = null, $code = null, $humanReadableMessage = null) : self
    {
        return self::deny(
            message: $message,
            code: $code,
            humanReadableMessage: $humanReadableMessage
        )->withStatus($status);
    }


    public static function denyAsNotFound($message = null, $code = null, $humanReadableMessage = null) : self
    {
        return self::denyWithStatus(
            status: 404,
            message: $message,
            code: $code,
            humanReadableMessage: $humanReadableMessage
        );
    }
}
