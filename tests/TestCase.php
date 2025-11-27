<?php

namespace Code16\OzuClient\Tests;

use Code16\OzuClient\OzuServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Code16\\OzuClient\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            OzuServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('ozu-client.api_host', 'http://ozu.test');
        config()->set('ozu-client.api_version', 'v1');
        config()->set('ozu-client.api_key', 'api_key');
        config()->set('ozu-client.website_key', 'test');

        /*
        $migration = include __DIR__.'/../database/migrations/create_ozu-client_table.php.stub';
        $migration->up();
        */
    }
}
