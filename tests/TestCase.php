<?php

namespace Wixiweb\WixiwebLaravel\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Wixiweb\WixiwebLaravel\WixiwebServiceProvider;
use function Orchestra\Testbench\workbench_path;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected bool $seed = true;

    protected function getPackageProviders($app,) : array
    {
        return [
            WixiwebServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }


}
