<?php

namespace Code16\OzuClient;

use Code16\OzuClient\Deploy\DeployServiceProvider;
use Code16\OzuClient\Support\Pagination\StaticLengthAwarePaginator;
use Code16\OzuClient\Support\Pagination\StaticPaginator;
use Code16\OzuClient\Support\Thumbnails\CdnThumbnail;
use Code16\OzuClient\Support\Thumbnails\LocalThumbnail;
use Code16\OzuClient\Support\Thumbnails\Thumbnail;
use Code16\OzuClient\View\Components\Content;
use Code16\OzuClient\View\Components\File;
use Code16\OzuClient\View\Components\Image;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class OzuServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ozu-client')
            ->hasMigrations(['create_ozu_tables'])->runsMigrations()
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
                apiHost: config('ozu-client.api_host'),
                apiKey: config('ozu-client.api_key'),
                apiVersion: config('ozu-client.api_version'),
                websiteKey: config('ozu-client.website_key'),
            );
        });

        $this->app->bind(Paginator::class, StaticPaginator::class);
        $this->app->bind(LengthAwarePaginator::class, StaticLengthAwarePaginator::class);
        $this->app->bind(Thumbnail::class, function ($app) {
            return $app->environment('production')
                ? $app->make(CdnThumbnail::class)
                : $app->make(LocalThumbnail::class);
        });

        $this->app->register(DeployServiceProvider::class);
    }

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ozu');

        $this->publishes([
            __DIR__.'/../resources/views/components/file.blade.php' => resource_path('views/vendor/ozu/components/file.blade.php'),
            __DIR__.'/../resources/views/components/image.blade.php' => resource_path('views/vendor/ozu/components/image.blade.php'),
        ], 'ozu-views');

        Blade::componentNamespace('Code16\\OzuClient\\View\\Components\\Content', 'ozu-content');
        Blade::component(Content::class, 'ozu-content');
        Blade::component(Image::class, 'ozu-image');
        Blade::component(File::class, 'ozu-file');

        Paginator::currentPageResolver(function () {
            return request()->route()->parameter('page');
        });

        Relation::enforceMorphMap(
            collect(config('ozu-client.collections'))
                ->mapWithKeys(fn ($className) => [
                    (new $className)->ozuCollectionKey() => $className
                ])
                ->toArray()
        );
    }
}
