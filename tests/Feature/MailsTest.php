<?php

use App\Mails\TestMail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;
use Wixiweb\WixiwebLaravel\Exceptions\RunTimeMailableException;
use Wixiweb\WixiwebLaravel\Mail\ExceptionMail;

function throwExceptionsAndTryToSendMail() {
    Mail::fake();

    $mailableException = new RunTimeMailableException('This is a test');
    report($mailableException);

    $runtimeException = new RuntimeException('This is a test');
    report($runtimeException);
}

test('Mailable exception should send a mail', function () {
    config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);

    throwExceptionsAndTryToSendMail();

    Mail::assertSent(ExceptionMail::class, 'exceptions@example.com');
    Mail::assertSent(ExceptionMail::class, function(ExceptionMail $exceptionMail) {
        expect($exceptionMail->exceptionGlobalContext)->toBeArray()->not->toBeEmpty();
        return true;
    });
    Mail::assertSentCount(1);
});

test('Mailable exception should not send a mail',  function () {
    config()->set('wixiweb.logging.mail.recipients', []);

    throwExceptionsAndTryToSendMail();

    Mail::assertNotSent(ExceptionMail::class, 'exceptions@example.com');
    Mail::assertNothingSent();
});

test('Mails are redirected', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['force@example.com']);
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails are not redirected', function () {
    config()->set('wixiweb.mail.to', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['toto@example.com']);
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails have bcc', function () {
    config()->set('wixiweb.mail.bcc', ['forcebcc@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getBcc());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['forcebcc@example.com']);
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails dont have bcc', function () {
    config()->set('wixiweb.mail.bcc', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getBcc());
        expect($addresses)->toBeArray()->toHaveCount(0);
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails have tags', function () {
    config()->set('wixiweb.mail.tags', ['toto', 'tutu']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeString()->toContain('toto', 'tutu');
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails dont have tags', function () {
    config()->set('wixiweb.mail.bcc', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeNull();
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails are sent to whitelist', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);
    config()->set('wixiweb.mail.whitelist', ['test@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['test@example.com']);
        return false;
    });

    Mail::to('test@example.com')->sendNow(new TestMail());
});

test('Mails are redirected if not whitelist', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);
    config()->set('wixiweb.mail.whitelist', ['test@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['force@example.com']);
        return false;
    });

    Mail::to('test2@example.com')->sendNow(new TestMail());
});
