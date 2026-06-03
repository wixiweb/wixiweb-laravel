<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('AUTH context defaults to guest values during an HTTP request', function () {
    $response = $this->get('/__wixiweb/context/guest?filter=recent');

    $response
        ->assertOk()
        ->assertJsonPath('AUTH.authenticated', false)
        ->assertJsonPath('AUTH.user', null)
        ->assertJsonPath('HTTP.url', 'http://localhost/__wixiweb/context/guest?filter=recent')
        ->assertJsonPath('HTTP.route.name', 'wixiweb.context.guest')
        ->assertJsonPath('HTTP.route.path', '__wixiweb/context/guest');
});

test('AUTH context is updated when an authenticated user is resolved', function () {
    $response = $this->get('/__wixiweb/context/authenticated');

    $response
        ->assertOk()
        ->assertJsonPath('AUTH.authenticated', true)
        ->assertJsonPath('AUTH.user', 123)
        ->assertJsonPath('HTTP.route.name', 'wixiweb.context.authenticated')
        ->assertJsonPath('HTTP.route.path', '__wixiweb/context/authenticated');
});

test('AUTH context stays authenticated across multiple pages after a session login', function () {
    $user = User::factory()->create([
        'email' => 'session-navigation@example.com',
    ]);

    $loginResponse = $this->get("/__wixiweb/context/session-login/{$user->getKey()}");

    $loginResponse->assertRedirect('/__wixiweb/context/authenticated/page-one');

    $sessionCookie = $loginResponse->getCookie(config('session.cookie'));

    expect($sessionCookie)->not->toBeNull();

    $this->withCookie($sessionCookie->getName(), $sessionCookie->getValue());

    $pageOneResponse = $this->get('/__wixiweb/context/authenticated/page-one');
    $pageTwoResponse = $this->get('/__wixiweb/context/authenticated/page-two?section=security');

    $pageOneResponse
        ->assertOk()
        ->assertJsonPath('AUTH.authenticated', true)
        ->assertJsonPath('AUTH.user', $user->getKey())
        ->assertJsonPath('HTTP.route.name', 'wixiweb.context.authenticated.page-one')
        ->assertJsonPath('HTTP.route.path', '__wixiweb/context/authenticated/page-one');

    $pageTwoResponse
        ->assertOk()
        ->assertJsonPath('AUTH.authenticated', true)
        ->assertJsonPath('AUTH.user', $user->getKey())
        ->assertJsonPath('HTTP.url', 'http://localhost/__wixiweb/context/authenticated/page-two?section=security')
        ->assertJsonPath('HTTP.route.name', 'wixiweb.context.authenticated.page-two')
        ->assertJsonPath('HTTP.route.path', '__wixiweb/context/authenticated/page-two');
});

test('le contexte HTTP.FILES est sérialisable en JSON quand une exception est déclenchée pendant un upload', function () {
    $uploadedFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->post('/__wixiweb/context/upload', [
        'document' => $uploadedFile,
    ]);

    $response->assertOk();

    $filesContext = $response->json('context.HTTP.FILES.document');

    expect($filesContext)->toBeArray()
        ->and($filesContext)->toHaveKey('name', 'document.pdf')
        ->and($filesContext)->toHaveKey('type')
        ->and($filesContext)->toHaveKey('size');
});

test('CLI context is filled when an artisan command starts', function () {
    $path = storage_path('app/wixiweb-test-cli-context.json');

    File::delete($path);

    $this->artisan('wixiweb:test-cli-context', [
        'name' => 'wixiweb',
        'nickname' => 'wixi',
        'status' => 'ready',
        'tags' => ['alpha', 'beta'],
        '--flag' => true,
        '--label' => 'demo',
        '--id' => ['7', '9'],
        '--qualified' => 'strict',
        '--ansi' => false,
    ])->assertSuccessful();

    expect(File::exists($path))->toBeTrue();

    $cliContext = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

    expect($cliContext)->toBeArray()
        ->and($cliContext)->toHaveKey('command', 'wixiweb:test-cli-context')
        ->and($cliContext)->toHaveKey('arguments')
        ->and($cliContext)->toHaveKey('options')
        ->and($cliContext['arguments'])->toBeArray()
        ->and($cliContext['arguments'])->toMatchArray([
            'name' => 'wixiweb',
            'nickname' => 'wixi',
            'status' => 'ready',
            'tags' => ['alpha', 'beta'],
        ])
        ->and($cliContext['options'])->toBeArray()
        ->and($cliContext['options'])->toMatchArray([
            'flag' => true,
            'label' => 'demo',
            'note' => null,
            'mode' => 'sync',
            'id' => ['7', '9'],
            'queue' => ['default', 'high'],
            'qualified' => 'strict',
            'ansi' => false,
        ]);

});
