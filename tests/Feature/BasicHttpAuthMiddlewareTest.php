<?php

namespace Wixiweb\WixiwebLaravel\Tests\Feature;

use Illuminate\Http\Request;
use Wixiweb\WixiwebLaravel\Http\Middleware\BasicHttpAuthMiddleware;

function requestWithCredentials(?string $username, ?string $password): Request
{
    $server = [];

    if ($username !== null) {
        $server['PHP_AUTH_USER'] = $username;
        $server['PHP_AUTH_PW'] = $password;
    }

    return Request::create('/', 'GET', server: $server);
}

function handleBasicAuth(Request $request): int
{
    return (new BasicHttpAuthMiddleware())
        ->handle($request, fn () => response('OK'))
        ->getStatusCode();
}

it('rejects requests without credentials when none are configured', function () {
    config(['wixiweb.basic_auth.username' => null, 'wixiweb.basic_auth.password' => null]);

    expect(handleBasicAuth(requestWithCredentials(null, null)))->toBe(401);
});

it('rejects empty credentials when none are configured', function () {
    config(['wixiweb.basic_auth.username' => '', 'wixiweb.basic_auth.password' => '']);

    expect(handleBasicAuth(requestWithCredentials('', '')))->toBe(401);
});

it('rejects wrong credentials', function () {
    config(['wixiweb.basic_auth.username' => 'user', 'wixiweb.basic_auth.password' => 'secret']);

    expect(handleBasicAuth(requestWithCredentials('user', 'wrong')))->toBe(401);
});

it('accepts valid credentials', function () {
    config(['wixiweb.basic_auth.username' => 'user', 'wixiweb.basic_auth.password' => 'secret']);

    expect(handleBasicAuth(requestWithCredentials('user', 'secret')))->toBe(200);
});

it('lets every request through in local environment', function () {
    app()['env'] = 'local';
    config(['wixiweb.basic_auth.username' => null, 'wixiweb.basic_auth.password' => null]);

    expect(handleBasicAuth(requestWithCredentials(null, null)))->toBe(200);
});
