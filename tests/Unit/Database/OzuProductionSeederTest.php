<?php

use Code16\OzuClient\Support\Database\OzuProductionSeeder;
use Code16\OzuClient\Tests\Fixtures\DummyTestModel;

use function Pest\testDirectory;

it('allows users to seed models in production', function () {
    Http::fake();
    config()->set('ozu-client.api_host', 'http://ozu.test');
    config()->set('ozu-client.api_key', 'api_key');
    config()->set('ozu-client.api_version', 'v1');
    config()->set('ozu-client.website_key', 'key');

    $seeder = new class extends OzuProductionSeeder
    {
        public function run()
        {
            $this->createInOzu(DummyTestModel::make([
                'title' => 'Project 1',
            ]))->id();
        }
    };

    $seeder->run();
    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return str($request->url())->startsWith(sprintf('http://ozu.test/api/v1/key/collections/%s/seed', app(DummyTestModel::class)->ozuCollectionKey()));
    });
});

it('allows users to seed images on models in production', function () {
    Http::fake();
    config()->set('ozu-client.api_host', 'http://ozu.test');
    config()->set('ozu-client.api_key', 'api_key');
    config()->set('ozu-client.api_version', 'v1');
    config()->set('ozu-client.website_key', 'key');

    $seeder = new class extends OzuProductionSeeder
    {
        public function run()
        {
            $this->createInOzu(DummyTestModel::make([
                'title' => 'Project 1',
            ]))->withFile('cover', testDirectory('Fixtures/philippe.jpg'), forceId: 5);
        }
    };

    $seeder->run();
    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return str($request->url())->startsWith(sprintf('http://ozu.test/api/v1/key/collections/%s/seed/5/file', app(DummyTestModel::class)->ozuCollectionKey()));
    });
});
