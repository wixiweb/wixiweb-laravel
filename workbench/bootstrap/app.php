<?php

use App\Exception\CustomMailableExceptionInterface;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Wixiweb\WixiwebLaravel\Wixiweb;
use function Orchestra\Testbench\default_skeleton_path;

return Application::configure(basePath: $APP_BASE_PATH ?? default_skeleton_path())
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        config()->set('wixiweb.logging.mail.exceptions', [
            InvalidArgumentException::class,
            CustomMailableExceptionInterface::class,
            ErrorException::class,
        ]);
        Wixiweb::configureExceptionHandler($exceptions);
    })->create();
