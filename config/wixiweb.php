<?php

use Illuminate\Support\Str;

return [
    'strict_model' => true,
    'mail' => [
        'to' => env('APP_MAIL_TO') !== null
            ? Str::of(env('APP_MAIL_TO'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
        'bcc' => env('APP_MAIL_BCC') !== null
            ? Str::of(env('APP_MAIL_BCC'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
        'tags' => (env('APP_MAIL_TAGS') !== null)
            ? Str::of(env('APP_MAIL_TAGS'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
        'whitelist' => (env('APP_MAIL_WHITELIST') !== null)
            ? Str::of(env('APP_MAIL_WHITELIST'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
            : [],
    ],
    'logging' => [
        'mail' => [
            'recipients' => env('LOG_MAIL_RECIPIENTS') !== null
                ? Str::of(env('LOG_MAIL_RECIPIENTS'))->squish()->explode(',')->filter()->map(fn(string $string) => trim($string))->all()
                : [],
            'exceptions' => [
                Error::class,
                ErrorException::class,
                PDOException::class,
            ],
        ],
        'context' => [
            // Chemins en dot-notation à masquer.
            // Seule la valeur exacte à ce chemin est masquée si elle est truthy.
            'hidden_fields' => [
                'HTTP.POST.password',
                'HTTP.POST.password_confirmation',
                'HTTP.POST.current_password',
                'HTTP.POST._token',
                'HTTP.GET.password',
                'HTTP.GET.password_confirmation',
                'HTTP.GET.current_password',
            ],
            // FQCN de filtres implémentant ContextFilterInterface, appliqués après la redaction.
            'filters' => [],
        ],
    ],
    'basic_auth' => [
        'username' => env('APP_BASIC_AUTH_USERNAME'),
        'password' => env('APP_BASIC_AUTH_PASSWORD'),
    ],
];
