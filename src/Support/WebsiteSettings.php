<?php

namespace Code16\OzuClient\Support;

use Code16\OzuClient\Facades\Ozu;
use Illuminate\Support\Facades\Cache;

abstract class WebsiteSettings
{
    private bool $loaded = false;

    public function __construct()
    {
//        $this->loadValues();
    }

    private function loadValues(): self
    {
        if($this->loaded) {
            return $this;
        }

//        if(!Ozu::shouldCache()) {
//            Cache::forget('ozu-settings');
//        }
//
//        $values = Cache::rememberForever('ozu-settings', function () {
//            return Ozu::getSettings();
//        });

        $values = Ozu::getSettings();

        $this->loaded = true;

        foreach($values as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }
}
