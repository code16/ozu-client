<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use ZipArchive;

class FetchDataFromOzu extends Command
{
    protected $signature = 'ozu:fetch-ozu-data';

    protected $description = 'Gets Ozu’s CMS data and replaces your local database and assets with it.';

    private string $databaseDumpPath;

    private string $assetsZipPath;

    private string $assetsExtractPath;

    public function handle(Client $ozuClient): int
    {

        $this->warn('/!\\ This action will erase your local database and assets.');

        if (!$this->confirm('Are you sure you want to continue? This cannot be undone.')) {
            return self::SUCCESS;
        }

        $this->initializePaths();

        if (!$this->downloadDatabase($ozuClient)) {
            return self::FAILURE;
        }

        if (!$this->importDatabase()) {
            return self::FAILURE;
        }

        if (!$this->downloadAssets($ozuClient)) {
            return self::FAILURE;
        }

        if (!$this->extractAssets()) {
            return self::FAILURE;
        }

        $this->cleanTemporaryFiles();
        $this->info('✅ Ozu data successfully imported.');

        return self::SUCCESS;
    }

    private function initializePaths(): void
    {
        $this->databaseDumpPath = storage_path('app/tmp/ozu.sql');
        $this->assetsZipPath = storage_path('app/tmp/ozu-assets.zip');
        $this->assetsExtractPath = storage_path('app/public/data/'.config('ozu-client.website_key').'/');
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

        $process = Process::fromShellCommandline(sprintf(
            'mysql -h%s -P%s -u%s %s < %s',
            $config['host'],
            $config['port'],
            $config['username'],
            $config['database'],
            $this->databaseDumpPath
        ));

        $process->setEnv(['MYSQL_PWD' => $config['password']]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('❌ Error importing the database:');
            $this->error($process->getErrorOutput());

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
            $zip = new ZipArchive();
            if ($zip->open($this->assetsZipPath) === true) {
                $zip->extractTo($this->assetsExtractPath);
                $zip->close();

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
}
