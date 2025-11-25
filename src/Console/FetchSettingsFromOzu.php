<?php

namespace Code16\OzuClient\Console;

use Code16\OzuClient\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchSettingsFromOzu extends Command
{
    protected $signature = 'ozu:fetch-settings {--ci}';

    protected $aliases = ['ozu:settings'];

    protected $description = 'Fetch settings values from Ozu.';

    public function handle(Client $ozuClient): int
    {
        if(config('ozu-client.settings') === null) {
            $this->error('OZU settings are not configured.');
            return $this->argument('ci') ? self::SUCCESS : self::FAILURE;
        }

        $settings = $ozuClient->fetchSettings();

        if ($settings === null) {
            $this->error('Ozu’s response was not successful.');
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

        $this->line('Settings fetched successfully');
        return self::SUCCESS;
    }
}
