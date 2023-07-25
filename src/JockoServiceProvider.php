<?php

namespace Code16\JockoClient;

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
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->singleton(Client::class, function () {
            return new Client(
                apiHost: config('jocko-client.api_host'),
                apiKey: config('jocko-client.api_key'),
                websiteKey: config('jocko-client.website_key'),
                shouldCache: config('jocko-client.should_cache'),
            );
        });
    }
}
