<?php

namespace Code16\OzuClient\Tests\Fixtures;

use Code16\OzuClient\OzuCms\OzuSettingsFormConfig;
use Code16\OzuClient\Support\Settings\OzuSiteSettings;

class DummySettings extends OzuSiteSettings
{
    public string $foo = 'default-foo';
    public string $bar = 'default-bar';

    public static function configureSettingsForm(OzuSettingsFormConfig $config): OzuSettingsFormConfig
    {
        return $config;
    }
}
