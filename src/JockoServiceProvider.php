<?php

namespace Code16\JockoClient;

use Code16\JockoClient\Support\Pagination\StaticLengthAwarePaginator;
use Code16\JockoClient\Support\Pagination\StaticPaginator;
use Code16\JockoClient\Support\Thumbnails\CdnThumbnail;
use Code16\JockoClient\Support\Thumbnails\LocalThumbnail;
use Code16\JockoClient\Support\Thumbnails\Thumbnail;
use Code16\JockoClient\View\Components\Content;
use Code16\JockoClient\View\Components\File;
use Code16\JockoClient\View\Components\Image;
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
            ->hasMigrations(['create_jocko_tables'])->runsMigrations()
            ->hasCommands([
                Console\ConfigureCmsCommand::class,
            ])
            ->hasConfigFile();
    }

    public function register()
    {
        parent::register();

        $this->app->singleton(ClientV3::class, function () {
            return new ClientV3(
                apiHost: config('jocko-client.api_host'),
                apiKey: config('jocko-client.api_key'),
                apiVersion: config('jocko-client.api_version'),
                websiteKey: config('jocko-client.website_key'),
            );
        });

        $this->app->bind(Paginator::class, StaticPaginator::class);
        $this->app->bind(LengthAwarePaginator::class, StaticLengthAwarePaginator::class);
        $this->app->bind(Thumbnail::class, function ($app) {
            return $app->environment('production')
                ? $app->make(CdnThumbnail::class)
                : $app->make(LocalThumbnail::class);
        });
    }

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'jocko');

        $this->publishes([
            __DIR__.'/../resources/views/components/file.blade.php' => resource_path('views/vendor/jocko/components/file.blade.php'),
            __DIR__.'/../resources/views/components/image.blade.php' => resource_path('views/vendor/jocko/components/image.blade.php'),
        ], 'jocko-views');

        Blade::componentNamespace('Code16\\JockoClient\\View\\Components\\Content', 'jocko-content');
        Blade::component(Content::class, 'jocko-content');
        Blade::component(Image::class, 'jocko-image');
        Blade::component(File::class, 'jocko-file');

        Paginator::currentPageResolver(function () {
            return request()->route()->parameter('page');
        });
    }
}
