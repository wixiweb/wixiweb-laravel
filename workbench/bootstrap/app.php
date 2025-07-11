<?php

use App\Exception\CustomMailableExceptionInterface;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Wixiweb\WixiwebLaravel\Wixiweb;
use function Orchestra\Testbench\default_skeleton_path;

return Application::configure(basePath: $APP_BASE_PATH ?? default_skeleton_path())
    ->withExceptions(function (Exceptions $exceptions) {
        config()->set('wixiweb.logging.mail.exceptions', [
            InvalidArgumentException::class,
            CustomMailableExceptionInterface::class,
            ErrorException::class,
        ]);
        Wixiweb::configureExceptionHandler($exceptions);
    })->create();
