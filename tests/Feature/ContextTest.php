<?php

use Illuminate\Support\Facades\File;

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
