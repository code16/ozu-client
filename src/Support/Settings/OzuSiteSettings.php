<?php

namespace Code16\OzuClient\Support\Settings;

use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuSettingsFormConfig;
use Illuminate\Support\Facades\Cache;

abstract class OzuSiteSettings
{
    public function __get(string $name) {
        $prefix = class_basename(static::class).'_';

        $cachedValue = Cache::get($prefix.$name);

        return $cachedValue
            ?: (
                property_exists($this, $name)
                    ? $this->$name
                    : (method_exists($this, $name) ? $this->$name() : null)
            );
    }

    abstract public static function configureSettingsForm(OzuSettingsFormConfig $config): OzuSettingsFormConfig;
}
