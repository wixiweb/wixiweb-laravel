<?php

test('Prevent destructive commands', function() {
    config()->set('wixiweb.prohibit_destructive_commands_handler', function() {
        return true;
    });

    $prohibitDestructiveCommandsHandler = config('wixiweb.prohibit_destructive_commands_handler');
    if (is_callable($prohibitDestructiveCommandsHandler)) {
        DB::prohibitDestructiveCommands($prohibitDestructiveCommandsHandler($this->app));
    }

    $code = Artisan::call('migrate:fresh');
    expect($code)->toBe(1);
});

test('Dont prevent destructive commands', function() {
    config()->set('wixiweb.prohibit_destructive_commands_handler', function() {
        return false;
    });

    $prohibitDestructiveCommandsHandler = config('wixiweb.prohibit_destructive_commands_handler');
    if (is_callable($prohibitDestructiveCommandsHandler)) {
        DB::prohibitDestructiveCommands($prohibitDestructiveCommandsHandler($this->app));
    }

    $code = Artisan::call('migrate:fresh');
    expect($code)->toBe(0);
});
