<?php

use App\Exception\CustomMailableExceptionInterface;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Wixiweb\WixiwebLaravel\Wixiweb;
use function Orchestra\Testbench\default_skeleton_path;

return Application::configure(basePath: $APP_BASE_PATH ?? default_skeleton_path())
    ->withExceptions(function (Exceptions $exceptions) {
        Wixiweb::configureExceptionHandler(
            $exceptions,
            [
                InvalidArgumentException::class,
                CustomMailableExceptionInterface::class
            ]
        );
    })->create();
