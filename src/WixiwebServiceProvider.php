<?php

namespace Wixiweb\WixiwebLaravel;

use Carbon\CarbonImmutable;
use Closure;
use Composer\InstalledVersions;
use DateTimeInterface;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Date;
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
        $this->registerGlobalContext();
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

        Date::use(CarbonImmutable::class);

        Model::shouldBeStrict(config('wixiweb.strict_model'));

        // S'assure que les jobs ne laissent pas de transaction ouverte
        Queue::looping(static function () {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        });

        $this->publishes([
            __DIR__.'/../config/wixiweb.php' => config_path('wixiweb.php'),
        ], 'wixiweb-config');
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

        Event::listen(CommandStarting::class, static function (CommandStarting $event,) {
            $arguments = $event->input->getArguments();
            if ($event->input->hasArgument('command')) {
                unset($arguments['command']);
            }

            Context::add([
                'CLI' => [
                    'command' => $event->command,
                    'arguments' => $arguments,
                    'options' => $event->input->getOptions(),
                ]
            ]);
        });

        Event::listen(RouteMatched::class, static function (RouteMatched $event,) {
            $routeAction = $event->route->getAction();

            if (isset($routeAction['uses']) && $routeAction['uses'] instanceof Closure) {
                $routeAction['uses'] = 'Closure';
            }

            Context::add([
                'HTTP' => [
                    'auth' => [
                        'is_authenticated' => $event->request->user() !== null,
                        'id' => $event->request->user()?->id,
                    ],
                    'url' => $event->request->fullUrl(),
                    'GET' => $event->request->query(),
                    'POST' => $event->request->post(),
                    'FILES' => $event->request->allFiles(),
                    'route' => [
                        'name' => $event->route->getName(),
                        'path' => $event->route->uri(),
                        'parameters' => $event->route->parameters(),
                        ...$routeAction,
                    ]
                ]
            ]);
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

    private function registerGlobalContext() : void
    {
        Context::add([
            'now' => now()->format(DateTimeInterface::ATOM),
            'env' => config('app.env'),
        ]);
    }
}
