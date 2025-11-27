<?php

namespace Code16\OzuClient;

use Code16\OzuClient\Support\Pagination\StaticLengthAwarePaginator;
use Code16\OzuClient\Support\Pagination\StaticPaginator;
use Code16\OzuClient\Support\Thumbnails\ImageKitThumbnail;
use Code16\OzuClient\Support\Thumbnails\KeyCdnThumbnail;
use Code16\OzuClient\Support\Thumbnails\LocalThumbnail;
use Code16\OzuClient\Support\Thumbnails\Thumbnail;
use Code16\OzuClient\View\Components\Content;
use Code16\OzuClient\View\Components\Embeds\File;
use Code16\OzuClient\View\Components\Embeds\Image;
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
                Console\FetchDataFromOzu::class,
                Console\FetchSettingsFromOzu::class,
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
            if (!$app->environment('production') || !config('ozu-client.cdn_url')) {
                return $app->make(LocalThumbnail::class);
            }

            // Have to rely on the URL to determine the CDN provider for now,
            // because we are limited to 10 params for the deployment script :/
            if (str(config('ozu-client.cdn_url'))->contains('kxcdn.com')) {
                return $app->make(KeyCdnThumbnail::class);
            }

            return $app->make(ImageKitThumbnail::class);
        });
    }

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ozu');

        $this->publishes([
            __DIR__.'/../resources/views/components/embeds' => resource_path('views/vendor/ozu/components/embeds'),
        ], 'ozu-views');

        Blade::componentNamespace('Code16\\OzuClient\\View\\Components\\Content', 'ozu-content');
        Blade::component(Content::class, 'ozu-content');
        Blade::component(Image::class, 'ozu-content-image');
        Blade::component(File::class, 'ozu-content-file');
        Blade::anonymousComponentPath(resource_path('views/vendor/ozu/components/embeds'));
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components/embeds');

        Paginator::currentPageResolver(function () {
            return request()->route()->parameter('page');
        });

        // Use Ozu's collectionKey as the morphMap key
        Relation::enforceMorphMap(
            collect(config('ozu-client.collections'))
                ->mapWithKeys(fn (string $className) => [
                    (new $className())->ozuCollectionKey() => $className,
                ])
                ->toArray()
        );
    }
}
