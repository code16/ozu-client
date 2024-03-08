<?php

namespace Code16\JockoClient\Deploy;

use Code16\JockoClient\Deploy\Jobs\CrawlSiteHandler;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class DeployServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(RoutingServiceProvider::class);
    }

    public function boot()
    {
        $this->app['events']->listen(function (CommandStarting $event) {
            if($event->command === 'export') {
                Artisan::call('cache:clear', [], $event->output);
            }
        });

        $this->app[Dispatcher::class]->map([
            \Spatie\Export\Jobs\CrawlSite::class => CrawlSiteHandler::class,
        ]);
    }
}
