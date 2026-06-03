<?php

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Route;

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
