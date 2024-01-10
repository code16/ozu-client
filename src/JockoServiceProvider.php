<?php

namespace Code16\JockoClient;

use Code16\JockoClient\Http\Middleware\PreviewAuthenticate;
use Code16\JockoClient\Services\Auth\PreviewGuard;
use Illuminate\Contracts\Http\Kernel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class JockoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('jocko-client')
            ->hasViews('jocko')
            ->hasRoute('web')
            ->hasConfigFile();
    }

    public function register()
    {
        parent::register();

        $this->app->singleton(Client::class, function () {
            return new Client(
                apiHost: config('jocko-client.api_host'),
                apiKey: config('jocko-client.api_key'),
                websiteKey: config('jocko-client.website_key'),
                shouldCache: config('jocko-client.should_cache'),
                isPreview: config('jocko-client.preview'),
            );
        });
    }

    public function boot()
    {
        parent::boot();

        if(config('jocko-client.preview')) {
            config()->set('auth.guards.jocko-preview', [
                'driver' => 'jocko-preview',
            ]);

            $this->app['auth']->extend('jocko-preview', function ($app, $name, array $config) {
                return new PreviewGuard($app['session.store']);
            });

            $this->app[Kernel::class]->appendMiddlewareToGroup('web', PreviewAuthenticate::class);
        }
    }
}
