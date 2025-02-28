<?php

use App\Models\User;
use App\Notifications\TestMailNotification;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use function Orchestra\Testbench\artisan;

test('Notifications are redirected', function () {

    config()->set('wixiweb.mail.to', ['force@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['force@example.com']);
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});

test('Notifications are not redirected', function () {
    config()->set('wixiweb.mail.to', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getTo());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['toto@example.com']);
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});

test('Notifications have bcc', function () {
    config()->set('wixiweb.mail.bcc', ['forcebcc@example.com']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getBcc());
        expect($addresses)->toBeArray()->toHaveCount(1)->toMatchArray(['forcebcc@example.com']);
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});

test('Notifications dont have bcc', function () {
    config()->set('wixiweb.mail.bcc', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $addresses = array_map(static function (Address $address,) {
            return $address->toString();
        }, $event->message->getBcc());
        expect($addresses)->toBeArray()->toHaveCount(0);
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});

test('Notifications have tags', function () {
    config()->set('wixiweb.mail.tags', ['toto', 'tutu']);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeString()->toContain('toto', 'tutu');
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});

test('Notifications dont have tags', function () {
    config()->set('wixiweb.mail.bcc', []);

    Event::listen(MessageSending::class, static function (MessageSending $event) {
        $tags = $event->message->getHeaders()->get('X-Tags')?->toString();
        expect($tags)->toBeNull();
        return false;
    });

    $user = User::query()->first();
    expect($user)->not->toBeNull();
    $user->notifyNow(new TestMailNotification());
});
