<?php

use App\Exception\CustomMailableExceptionInterface;
use App\Mails\TestMail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;
use Wixiweb\WixiwebLaravel\Exceptions\RunTimeMailableException;
use Wixiweb\WixiwebLaravel\Mail\ExceptionMail;

function throwExceptionsAndTryToSendMail(bool $fake = true,)
{
    if ($fake === true) {
        Mail::fake();
    }

    $mailableException = new RunTimeMailableException('RunTimeMailableException. This is a test always sent');
    report($mailableException);

    $runtimeException = new RuntimeException('RuntimeException. This is a test never sent');
    report($runtimeException);

    $invalidArgumentException = new InvalidArgumentException('InvalidArgumentException. This is a test always sent');
    report($invalidArgumentException);

    $customClass = new class('$customClass. This is a test always sent') extends RuntimeException implements CustomMailableExceptionInterface {
    };
    report($customClass);
}

test('Mailable exception should send a mail', function () {
    config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);

    throwExceptionsAndTryToSendMail();

    Mail::assertSent(ExceptionMail::class, 'exceptions@example.com');
    Mail::assertSent(ExceptionMail::class, function (ExceptionMail $exceptionMail,) {
        expect($exceptionMail->exceptionGlobalContext)->toBeArray()->not->toBeEmpty();
        return true;
    });

    Mail::assertSentCount(3);
});

test('Mailable exception should not send a mail', function () {
    config()->set('wixiweb.logging.mail.recipients', []);

    throwExceptionsAndTryToSendMail();

    Mail::assertNotSent(ExceptionMail::class, 'exceptions@example.com');
    Mail::assertNothingSent();
});

test('Mails are redirected', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
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

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['toto@example.com']);
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails are redirected but not exceptions mails', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);
    config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);

    $adressesCount = [];
    Event::listen(MessageSending::class, static function (MessageSending $event) use (&$adressesCount) {
        foreach ($event->message->getTo() as $address) {
            if (array_key_exists($address->toString(), $adressesCount) === false) {
                $adressesCount[$address->toString()] = 0;
            }

            $adressesCount[$address->toString()]++;
        }

        return false;
    });

    throwExceptionsAndTryToSendMail(false);
    Mail::to('toto@example.com')->sendNow(new TestMail());

    expect($adressesCount)
        ->toBeArray()
        ->toHaveCount(2)
        ->toMatchArray([
            'force@example.com' => 1,
            'exceptions@example.com' => 3
        ]);
});

test('Mails have bcc', function () {
    config()->set('wixiweb.mail.bcc', ['forcebcc@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
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

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
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

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeString()->toContain('toto', 'tutu');
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails dont have tags', function () {
    config()->set('wixiweb.mail.bcc', []);

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeNull();
        return false;
    });

    Mail::to('toto@example.com')->sendNow(new TestMail());
});

test('Mails are sent to whitelist', function () {
    config()->set('wixiweb.mail.to', ['force@example.com']);
    config()->set('wixiweb.mail.whitelist', ['test@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
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

    Event::listen(MessageSending::class, static function (MessageSending $event,) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['force@example.com']);
        return false;
    });

    Mail::to('test2@example.com')->sendNow(new TestMail());
});

test('Error exceptions are sent by mail', function () {
    config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);
    Mail::fake();

    $test = ['a' => 1];
    try {
        $test['b'];
    }
    catch (ErrorException $exception) {
        report($exception);
    }

    Mail::assertSent(ExceptionMail::class, 'exceptions@example.com');
    Mail::assertSent(ExceptionMail::class, function (ExceptionMail $exceptionMail,) {
        expect($exceptionMail->exceptionGlobalContext)->toBeArray()->not->toBeEmpty();
        return true;
    });

    Mail::assertSentCount(1);
});
