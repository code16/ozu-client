<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Code16\OzuClient\Support\ZipExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;

class FetchDataFromOzu extends Command
{
    protected $signature = 'ozu:import {--settings : Import only settings values} {--force : Do not ask for confirmation} {--withoutAssets : Do not download assets}';
    protected $description = 'Gets Ozu’s CMS data and or settings and replaces your local database and assets with it.';
    private string $databaseDumpPath;
    private string $assetsZipPath;
    private string $assetsExtractPath;

    public function handle(Client $ozuClient): int
    {
        $this->newLine(2);

        if ($this->option('settings')) {
            // Fetch only ozu's settings
            return $this->fetchSettings($ozuClient);
        }

        $this->warn('⚠️  This action will erase your local database and assets.');

        if (!$this->option('force')) {
            if (!confirm(
                'Are you sure you want to continue? This cannot be undone.',
                default: false,
            )) {
                return self::SUCCESS;
            }
        }

        $this->initializePaths($ozuClient);

        if (!$this->downloadDatabase($ozuClient)) {
            return self::FAILURE;
        }

        if (!$this->importDatabase()) {
            return self::FAILURE;
        }

        if (!$this->option('withoutAssets')) {
            if (!$this->downloadAssets($ozuClient)) {
                return self::FAILURE;
            }

            if (!$this->extractAssets()) {
                return self::FAILURE;
            }
        }

        $this->cleanTemporaryFiles();

        if ($this->fetchSettings($ozuClient) === self::FAILURE) {
            return self::FAILURE;
        }

        $this->info('✅ Ozu data successfully imported.');

        return self::SUCCESS;
    }

    private function initializePaths(Client $ozuClient): void
    {
        $websiteKey = config('ozu-client.website_key') ?? $ozuClient->getWebsiteKey();
        $this->databaseDumpPath = storage_path('app/tmp/ozu.sql');
        $this->assetsZipPath = storage_path('app/tmp/ozu-assets.zip');
        $this->assetsExtractPath = storage_path('app/public/data/'.$websiteKey.'/');
    }

    private function downloadDatabase(Client $ozuClient): bool
    {
        $this->info('⬇️ Downloading Ozu database...');
        $dbPath = $ozuClient->downloadOzuDatabase();

        if (!$dbPath) {
            $this->error('❌ Failed to download the Ozu database.');

            return false;
        }

        $this->databaseDumpPath = $dbPath;

        return true;
    }

    private function importDatabase(): bool
    {
        $this->info('🧹 Wiping local database...');
        Artisan::call('db:wipe');

        $this->info('🛠 Importing Ozu database...');
        $config = config('database.connections.mysql');

        $result = Process::env(['MYSQL_PWD' => $config['password']])
            ->run(sprintf(
                'mysql -h%s -P%s -u%s %s < %s',
                $config['host'],
                $config['port'],
                $config['username'],
                $config['database'],
                $this->databaseDumpPath
            ));

        if (!$result->successful()) {
            $this->error('❌ Error importing the database:');
            $this->error($result->errorOutput());

            return false;
        }

        $this->info('✅ Database imported successfully.');

        return true;
    }

    private function downloadAssets(Client $ozuClient): bool
    {
        $this->info('⬇️ Downloading Ozu assets...');
        $zipPath = $ozuClient->downloadOzuAssets();

        if (!$zipPath) {
            $this->error('❌ Failed to download Ozu assets.');

            return false;
        }

        $this->assetsZipPath = $zipPath;

        return true;
    }

    private function extractAssets(): bool
    {
        $this->info('📦 Extracting Ozu assets...');

        try {
            if (app(ZipExtractor::class)->extract($this->assetsZipPath, $this->assetsExtractPath)) {
                return true;
            }

            $this->error('❌ Failed to open the zip file.');
        } catch (\Throwable $e) {
            $this->error('❌ Exception while extracting assets:');
            $this->error($e->getMessage());
        }

        return false;
    }

    private function cleanTemporaryFiles(): void
    {
        @unlink($this->databaseDumpPath);
        @unlink($this->assetsZipPath);
    }

    private function fetchSettings(Client $ozuClient): int
    {
        $this->info('⚙️ Importing Ozu settings...');

        if (config('ozu-client.settings') === null) {
            $this->info('❌ OZU settings are not configured.');

            return self::SUCCESS;
        }

        $settings = $ozuClient->fetchSettings();

        if ($settings === null) {
            $this->error('❌ Ozu’s response was not successful.');

            return self::FAILURE;
        }

        $prefix = class_basename(config('ozu-client.settings')).'_';

        foreach ($settings as $settingKey => $settingValue) {
            if (json_validate($settingValue)) {
                Cache::set($prefix.$settingKey, json_decode($settingValue, true));

                continue;
            }

            Cache::set($prefix.$settingKey, $settingValue);
        }

        $this->info('✅ Settings fetched successfully');

        return self::SUCCESS;
    }
}
