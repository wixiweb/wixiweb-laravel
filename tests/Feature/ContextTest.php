<?php

use App\Filters\AppendCustomDataFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Wixiweb\WixiwebLaravel\Mail\ExceptionMail;

uses(RefreshDatabase::class);

/**
 * Configure un canal de log "test_filter" écrivant dans un fichier temporaire,
 * vide ce fichier et retourne son chemin.
 */
function setUpTestFilterLogChannel(): string
{
    $logFile = storage_path('logs/test_filter.log');

    File::ensureDirectoryExists(dirname($logFile));
    File::delete($logFile);

    config()->set('logging.channels.test_filter', [
        'driver' => 'single',
        'path' => $logFile,
        'level' => 'debug',
    ]);

    return $logFile;
}

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

describe('filtrage du contexte (dot-notation)', function () {
    test('un champ sensible (chemin dot exact) est masqué dans le contexte filtré', function () {
        $response = $this->post('/__wixiweb/context/sensitive', [
            'password' => 'super-secret',
            'name' => 'John',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('HTTP.POST.password', '***')
            ->assertJsonPath('HTTP.POST.name', 'John');
    });

    test('le matching dot-notation est un chemin exact et ne masque pas une clé homonyme à une autre position', function () {
        $response = $this->post('/__wixiweb/context/sensitive', [
            'password' => 'super-secret',
            'profile' => ['password' => 'nested-not-masked'],
        ]);

        // 'HTTP.POST.password' est dans les defaults -> masqué.
        // 'HTTP.POST.profile.password' n'est PAS un chemin configuré -> conservé.
        $response
            ->assertOk()
            ->assertJsonPath('HTTP.POST.password', '***')
            ->assertJsonPath('HTTP.POST.profile.password', 'nested-not-masked');
    });

    test('une valeur falsy n\'est pas masquée', function () {
        // Le middleware ConvertEmptyStringsToNull transforme '' en null : valeur falsy,
        // donc Arr::get(...) est falsy et la redaction est ignorée (on garde null, pas '***').
        $response = $this->post('/__wixiweb/context/sensitive', [
            'password' => '',
            'name' => 'John',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('HTTP.POST.password', null)
            ->assertJsonPath('HTTP.POST.name', 'John');
    });

    test('un champ sensible passé en query string GET est masqué', function () {
        $response = $this->get('/__wixiweb/context/sensitive?password=super-secret&filter=recent');

        $response
            ->assertOk()
            ->assertJsonPath('HTTP.GET.password', '***')
            ->assertJsonPath('HTTP.GET.filter', 'recent');
    });

    test('un chemin custom ajouté à hidden_fields est masqué', function () {
        config()->set('wixiweb.logging.context.hidden_fields', ['HTTP.POST.api_key']);

        $response = $this->post('/__wixiweb/context/sensitive', [
            'api_key' => 'secret-api-key',
            'password' => 'visible-car-non-configuré',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('HTTP.POST.api_key', '***')
            ->assertJsonPath('HTTP.POST.password', 'visible-car-non-configuré');
    });

    test('les filtres custom de la config sont appliqués au contexte filtré', function () {
        config()->set('wixiweb.logging.context.filters', [AppendCustomDataFilter::class]);

        $response = $this->post('/__wixiweb/context/sensitive');

        $response
            ->assertOk()
            ->assertJsonPath('CUSTOM', 'injected');
    });
});

describe('filtrage du contexte — logs fichiers', function () {
    test('un champ sensible est masqué dans le log fichier', function () {
        $logFile = setUpTestFilterLogChannel();

        $this->post('/__wixiweb/test/log-sensitive', [
            'password' => 'super-secret',
            'name' => 'John',
        ])->assertOk();

        $contents = File::get($logFile);

        expect($contents)
            ->toContain('"password":"***"')
            ->not->toContain('super-secret');
    });

    test('les filtres custom de la config sont appliqués aux logs fichiers', function () {
        config()->set('wixiweb.logging.context.filters', [AppendCustomDataFilter::class]);

        $logFile = setUpTestFilterLogChannel();

        $this->post('/__wixiweb/test/log-with-custom-context')->assertOk();

        expect(File::get($logFile))->toContain('"CUSTOM":"injected"');
    });
});

describe('filtrage du contexte — mails d\'exception', function () {
    test('le contexte du mail d\'exception masque les champs sensibles et conserve le reste', function () {
        config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);
        Mail::fake();

        $this->post('/__wixiweb/test/exception-mail', [
            'password' => 'super-secret',
            'name' => 'John',
        ]);

        Mail::assertSent(ExceptionMail::class, function (ExceptionMail $mail) {
            expect($mail->exceptionGlobalContext)
                ->toHaveKey('HTTP')
                ->and(data_get($mail->exceptionGlobalContext, 'HTTP.POST.password'))->toBe('***')
                ->and(data_get($mail->exceptionGlobalContext, 'HTTP.POST.name'))->toBe('John');

            $html = $mail->render();
            expect($html)->not->toContain('super-secret');

            return true;
        });
    });

    test('les filtres custom de la config sont appliqués au contexte du mail', function () {
        config()->set('wixiweb.logging.mail.recipients', ['exceptions@example.com']);
        config()->set('wixiweb.logging.context.filters', [AppendCustomDataFilter::class]);
        Mail::fake();

        $this->post('/__wixiweb/test/exception-mail');

        Mail::assertSent(ExceptionMail::class, function (ExceptionMail $mail) {
            expect(data_get($mail->exceptionGlobalContext, 'CUSTOM'))->toBe('injected');

            return true;
        });
    });
});
