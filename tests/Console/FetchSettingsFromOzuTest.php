<?php

use Code16\OzuClient\Client as OzuClient;
use Code16\OzuClient\Console\FetchDataFromOzu;
use Code16\OzuClient\Tests\Fixtures\DummySettings;
use Illuminate\Support\Facades\Cache;
use Mockery as m;

it('succeeds when settings are not configured', function () {
    config(['ozu-client.settings' => null, 'cache.default' => 'array']);

    $this->artisan('ozu:import --settings')
        ->expectsOutput('❌ OZU settings are not configured.')
        ->assertExitCode(FetchDataFromOzu::SUCCESS);
});

it('fails when the Ozu client returns null (unsuccessful response)', function () {
    config(['ozu-client.settings' => DummySettings::class, 'cache.default' => 'array']);

    $mock = m::mock(OzuClient::class);
    $mock->shouldReceive('fetchSettings')->once()->andReturn(null);
    $this->app->instance(OzuClient::class, $mock);

    $this->artisan('ozu:import --settings')
        ->expectsOutput('❌ Ozu’s response was not successful.')
        ->assertExitCode(FetchDataFromOzu::FAILURE);
});

it('caches fetched settings with class-based prefix and decodes JSON values', function () {
    config(['ozu-client.settings' => DummySettings::class, 'cache.default' => 'array']);
    Cache::flush();

    $mock = m::mock(OzuClient::class);
    $mock->shouldReceive('fetchSettings')->once()->andReturn([
        'foo' => '"value-foo"',      // JSON encoded string
        'bar' => '{"nested":true}', // JSON object
        'other' => 'plain-value',     // not JSON
    ]);
    $this->app->instance(OzuClient::class, $mock);

    $this->artisan('ozu:import --settings')
        ->expectsOutput('✅ Settings fetched successfully')
        ->assertExitCode(FetchDataFromOzu::SUCCESS);

    expect(Cache::get('DummySettings_foo'))->toBe('value-foo');
    expect(Cache::get('DummySettings_bar'))->toBe(['nested' => true]);
    expect(Cache::get('DummySettings_other'))->toBe('plain-value');
});
