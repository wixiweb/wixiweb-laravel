<?php

namespace Wixiweb\WixiwebLaravel;

use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Wixiweb\WixiwebLaravel\Console\Commands\DbCreateCommand;

class WixiwebServiceProvider extends ServiceProvider
{
    public function boot() : void
    {
        $this->registerEvents();
        $this->registerViews();

        if ($this->app->runningInConsole()) {
            AboutCommand::add('Wixiweb laravel', fn() => [
                'version' => $this->getPackageVersion('wixiweb/wixiweb-laravel'),
            ]);

            $this->publishes([
                __DIR__.'/../config/wixiweb.php' => config_path('wixiweb.php'),
            ], 'wixiweb-config');

            $this->commands([
                DbCreateCommand::class,
            ]);
        }

        Number::useLocale(config('app.locale'));

        Model::shouldBeStrict(config('wixiweb.strict_model'));

        // S'assure que les jobs ne laissent pas de transaction ouverte
        Queue::looping(static function () {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        });
    }

    public function register() : void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/wixiweb.php', 'wixiweb',
        );
    }

    private function registerEvents() : void
    {
        Event::listen(MessageSending::class, static function (MessageSending $event,) {
            $whitelistedAddresses = [];

            foreach ($event->message->getTo() as $address) {
                if (in_array($address->toString(), config('wixiweb.mail.whitelist'), true)) {
                    $whitelistedAddresses[] = $address->toString();
                }
            }

            if (count($whitelistedAddresses) > 0) {
                $event->message->to(...$whitelistedAddresses);
                return;
            }

            if(count(config('wixiweb.mail.tags', [])) > 0)
            {
                $event->message->getHeaders()->addTextHeader('X-Tags', implode(',',config('wixiweb.mail.tags')));
            }

            if (count(config('wixiweb.mail.to', [])) > 0) {
                $event->message->to(...config('wixiweb.mail.to'));
            }

            if (count(config('wixiweb.mail.bcc', [])) > 0) {
                $event->message->bcc(...config('wixiweb.mail.bcc'));
            }
        });
    }

    private function registerViews() : void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wixiweb');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/wixiweb'),
        ]);
    }

    protected function getPackageVersion(string $package,): string
    {
        if (InstalledVersions::isInstalled($package)) {
            return InstalledVersions::getPrettyVersion($package);
        }

        return '<fg=yellow;options=bold>INCORRECT</>';
    }
}
