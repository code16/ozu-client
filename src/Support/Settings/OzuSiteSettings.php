<?php

namespace Code16\OzuClient\Support\Settings;

use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuSettingsFormConfig;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

abstract class OzuSiteSettings
{

    public function __construct()
    {
        $prefix = class_basename(static::class) . '_';

        foreach (get_object_vars($this) as $propertyName => $currentValue) {
            $this->$propertyName = Cache::get($prefix . $propertyName, $currentValue);
        }
    }

    public function get(string $name) {
        return $this->$name;
    }

    abstract public static function configureSettingsForm(OzuSettingsFormConfig $config): OzuSettingsFormConfig;
}
