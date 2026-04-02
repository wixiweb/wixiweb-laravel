<?php

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

Artisan::command(
    'wixiweb:test-cli-context
        {name}
        {nickname?}
        {status=active}
        {tags?*}
        {--flag}
        {--label=}
        {--note=}
        {--mode=sync}
        {--id=*}
        {--queue=*default,high}
        {--Q|qualified=}',
    function () {
    $path = storage_path('app/wixiweb-test-cli-context.json');

    Event::dispatch(new CommandStarting(
        $this->getName(),
        $this->input,
        $this->output,
    ));

    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode(Context::get('CLI'), JSON_THROW_ON_ERROR));
})->purpose('Write the CLI context to a JSON file for tests.');
