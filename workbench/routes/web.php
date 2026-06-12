<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Wixiweb\WixiwebLaravel\Wixiweb;

Route::get('/__wixiweb/context/guest', function () {
    return [
        'AUTH' => Context::get('AUTH'),
        'HTTP' => Arr::only(Context::get('HTTP'), ['url', 'route']),
    ];
})->name('wixiweb.context.guest');

Route::get('/__wixiweb/context/authenticated', function () {
    $user = new User();
    $user->forceFill(['id' => 123]);

    Auth::setUser($user);

    return [
        'AUTH' => Context::get('AUTH'),
        'HTTP' => Arr::only(Context::get('HTTP'), ['url', 'route']),
    ];
})->name('wixiweb.context.authenticated');

Route::get('/__wixiweb/context/session-login/{user}', function (User $user) {
    Auth::login($user);

    return redirect()->route('wixiweb.context.authenticated.page-one');
})->name('wixiweb.context.session-login');

Route::get('/__wixiweb/context/authenticated/page-one', function () {
    return [
        'AUTH' => Context::get('AUTH'),
        'HTTP' => Arr::only(Context::get('HTTP'), ['url', 'route']),
    ];
})->name('wixiweb.context.authenticated.page-one');

Route::get('/__wixiweb/context/authenticated/page-two', function () {
    return [
        'AUTH' => Context::get('AUTH'),
        'HTTP' => Arr::only(Context::get('HTTP'), ['url', 'route']),
    ];
})->name('wixiweb.context.authenticated.page-two');

Route::post('/__wixiweb/context/upload', function () {
    try {
        throw new \RuntimeException('Exception déclenché pendant un upload');
    } catch (\Throwable $exception) {
        $serializedContext = json_encode(Context::all(), JSON_THROW_ON_ERROR);

        return response()->json([
            'context' => json_decode($serializedContext, true),
            'exception' => $exception->getMessage(),
        ]);
    }
})->name('wixiweb.context.upload');

Route::match(['get', 'post'], '/__wixiweb/context/sensitive', function () {
    return Wixiweb::getFilteredContext();
})->name('wixiweb.context.sensitive');

Route::match(['get', 'post'], '/__wixiweb/test/log-sensitive', function () {
    Log::channel('test_filter')->info('Test filtrage logs');

    return response()->json(['ok' => true]);
})->name('wixiweb.test.log-sensitive');

Route::match(['get', 'post'], '/__wixiweb/test/log-with-custom-context', function () {
    Context::add([
        'APP' => ['api_key' => 'secret-api-key', 'user_id' => 42],
    ]);

    Log::channel('test_filter')->info('Log avec contexte custom');

    return response()->json(['ok' => true]);
})->name('wixiweb.test.log-with-custom-context');

Route::match(['get', 'post'], '/__wixiweb/test/exception-mail', function () {
    throw new \InvalidArgumentException('Test exception pour vérifier le filtrage du contexte dans le mail');
})->name('wixiweb.test.exception-mail');
