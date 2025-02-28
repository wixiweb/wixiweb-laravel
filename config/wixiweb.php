<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

return [
    'strict_model' => true,
    'prohibit_destructive_commands_handler' => function(Application $app) {
        return $app->environment('production');
    },
    'mail' => [
        'to' => env('APP_MAIL_TO') !== null
            ? Str::of(env('APP_MAIL_TO'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
        'bcc' => env('APP_MAIL_BCC') !== null
            ? Str::of(env('APP_MAIL_BCC'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
        'tags' => (env('APP_MAIL_TAGS') !== null)
            ? Str::of(env('APP_MAIL_TAGS'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : []
    ],
    'logging' => [
        'mail' => [
            'recipients' => env('LOG_MAIL_RECIPIENTS') !== null
                ? Str::of(env('LOG_MAIL_RECIPIENTS'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
                : [],
        ],
    ]
];
