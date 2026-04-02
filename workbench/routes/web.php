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
