<?php

namespace Code16\OzuClient\Tests\Console;

use Code16\OzuClient\Client;
use Code16\OzuClient\Console\FetchDataFromOzu;
use Code16\OzuClient\Support\ZipExtractor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

it('fetches database and assets from Ozu', function () {
    Storage::fake('local');
    Http::fake([
        '*/api/v1/websites/database' => Http::response('SQL DUMP', 200),
        '*/api/v1/websites/assets' => Http::response('ZIP CONTENT', 200),
        '*/api/v1/settings/fetch' => Http::response(['key' => 'value'], 200),
        '*/api/v1/website' => Http::response(['key' => 'test-website'], 200),
    ]);

    config([
        'ozu-client.api_host' => 'https://ozu.test',
        'ozu-client.api_version' => 'v1',
        'ozu-client.website_key' => 'test-website',
        'database.connections.mysql' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'database' => 'forge',
            'password' => '',
        ],
    ]);

    // Mock Artisan::call('db:wipe')
    // We can't easily mock Artisan facade here because it's final in Testbench
    // But we can check if it was called if we really need to.
    // For now let's just let it be or use a partial mock if possible.

    // Mock Process for mysql import
    Process::fake([
        'mysql *' => Process::result('', '', 0),
    ]);

    // Mock ZipExtractor
    $this->mock(ZipExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')->once()->andReturn(true);
    });

    $this->artisan('ozu:import', ['--force' => true])
        ->expectsOutput('⬇️ Downloading Ozu database...')
        ->expectsOutput('🧹 Wiping local database...')
        ->expectsOutput('🛠 Importing Ozu database...')
        ->expectsOutput('✅ Database imported successfully.')
        ->expectsOutput('⬇️ Downloading Ozu assets...')
        ->expectsOutput('📦 Extracting Ozu assets...')
        ->expectsOutput('✅ Ozu data successfully imported.')
        ->assertExitCode(FetchDataFromOzu::SUCCESS);
});

it('can skip assets import', function () {
    Storage::fake('local');
    Http::fake([
        '*/api/v1/websites/database' => Http::response('SQL DUMP', 200),
        '*/api/v1/website' => Http::response(['key' => 'test-website'], 200),
    ]);

    config([
        'ozu-client.api_host' => 'https://ozu.test',
        'ozu-client.api_version' => 'v1',
        'ozu-client.website_key' => 'test-website',
        'database.connections.mysql' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'database' => 'forge',
            'password' => '',
        ],
    ]);

    Process::fake([
        'mysql *' => Process::result('', '', 0),
    ]);

    $this->artisan('ozu:import', ['--force' => true, '--withoutAssets' => true])
        ->expectsOutput('⬇️ Downloading Ozu database...')
        ->expectsOutput('🧹 Wiping local database...')
        ->expectsOutput('🛠 Importing Ozu database...')
        ->expectsOutput('✅ Database imported successfully.')
        ->doesntExpectOutput('⬇️ Downloading Ozu assets...')
        ->expectsOutput('✅ Ozu data successfully imported.')
        ->assertExitCode(FetchDataFromOzu::SUCCESS);
});

it('handles database download failure', function () {
    Storage::fake('local');

    $this->mock(Client::class, function ($mock) {
        $mock->shouldReceive('downloadOzuDatabase')->once()->andReturn(null);
    });

    $this->artisan('ozu:import', ['--force' => true])
        ->expectsOutput('⬇️ Downloading Ozu database...')
        ->expectsOutput('❌ Failed to download the Ozu database.')
        ->assertExitCode(FetchDataFromOzu::FAILURE);
});

it('asks for confirmation when force option is not present', function () {
    Storage::fake('local');
    Http::fake([
        '*/api/v1/website' => Http::response(['key' => 'test-website'], 200),
    ]);

    $this->artisan('ozu:import')
        ->expectsConfirmation('Are you sure you want to continue? This cannot be undone.', 'no')
        ->assertExitCode(FetchDataFromOzu::SUCCESS);
});

it('handles asset extraction failure', function () {
    Storage::fake('local');
    Http::fake([
        '*/api/v1/websites/database' => Http::response('SQL DUMP', 200),
        '*/api/v1/websites/assets' => Http::response('ZIP CONTENT', 200),
        '*/api/v1/website' => Http::response(['key' => 'test-website'], 200),
    ]);

    config([
        'ozu-client.api_host' => 'https://ozu.test',
        'ozu-client.api_version' => 'v1',
        'ozu-client.website_key' => 'test-website',
        'database.connections.mysql' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'database' => 'forge',
            'password' => '',
        ],
    ]);

    Process::fake([
        'mysql *' => Process::result('', '', 0),
    ]);

    $this->mock(ZipExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')->once()->andReturn(false);
    });

    $this->artisan('ozu:import', ['--force' => true])
        ->expectsOutput('⬇️ Downloading Ozu database...')
        ->expectsOutput('🧹 Wiping local database...')
        ->expectsOutput('🛠 Importing Ozu database...')
        ->expectsOutput('✅ Database imported successfully.')
        ->expectsOutput('⬇️ Downloading Ozu assets...')
        ->expectsOutput('📦 Extracting Ozu assets...')
        ->expectsOutput('❌ Failed to open the zip file.')
        ->assertExitCode(FetchDataFromOzu::FAILURE);
});
