<?php

namespace Code16\JockoClient;

use Code16\JockoClient\Http\Middleware\PreviewAuthenticate;
use Code16\JockoClient\Services\Auth\PreviewGuard;
use Code16\JockoClient\Support\Pagination\StaticLengthAwarePaginator;
use Code16\JockoClient\Support\Pagination\StaticPaginator;
use Code16\JockoClient\View\Components\Content;
use Code16\JockoClient\View\Components\File;
use Code16\JockoClient\View\Components\Image;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
            ->hasRoute('web')
            ->hasMigrations(['create_jocko_tables'])->runsMigrations()
            ->hasCommands([
                Console\ConfigureCmsCommand::class,
            ])
            ->hasConfigFile();
    }

    public function register()
    {
        parent::register();

        $this->app->singleton(Client::class, function () {
            return new Client(
                apiHost: config('jocko-client.api_host'),
                apiKey: config('jocko-client.api_key'),
                apiVersion: config('jocko-client.api_version'),
                websiteKey: config('jocko-client.website_key'),
                shouldCache: config('jocko-client.should_cache'),
                isPreview: config('jocko-client.preview'),
            );
        });

        $this->app->bind(Paginator::class, StaticPaginator::class);
        $this->app->bind(LengthAwarePaginator::class, StaticLengthAwarePaginator::class);
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

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'jocko');

        $this->publishes([
            __DIR__.'/../resources/views/components/file.blade.php' => resource_path('views/vendor/jocko/components/file.blade.php'),
            __DIR__.'/../resources/views/components/image.blade.php' => resource_path('views/vendor/jocko/components/image.blade.php'),
        ], 'jocko-views');

        Blade::componentNamespace('Code16\\JockoClient\\View\\Components\\Content', 'jocko-content');
        Blade::component(Content::class, 'jocko-content');
        Blade::component(Image::class, 'jocko-image');
        Blade::component(File::class, 'jocko-file');
    }
}
