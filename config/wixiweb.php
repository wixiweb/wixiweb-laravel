<?php

use Illuminate\Support\Str;

return [
    'strict_model' => true,
    'prohibit_destructive_commands' => true,
    'mail' => [
        'to' => env('APP_MAIL_TO') !== null
            ? Str::of(env('APP_MAIL_TO'))->squish()->explode(',')->all()
            : null,
        'bcc' => env('APP_MAIL_BCC') !== null
            ? Str::of(env('APP_MAIL_BCC'))->squish()->explode(',')->all()
            : null,
        'tags' => (env('APP_MAIL_TAGS') !== null)
            ? Str::of(env('APP_MAIL_TAGS'))->squish()->explode(',')->all()
            : []
    ],
    'logging' => [
        'mail' => [
            'recipients' => env('LOG_MAIL_RECIPIENTS') !== null
                ? Str::of(env('LOG_MAIL_RECIPIENTS'))->squish()->explode(',')->all()
                : [],
        ],
    ]
];
