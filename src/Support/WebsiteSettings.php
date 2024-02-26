<?php

namespace Code16\JockoClient\Support;

use Code16\JockoClient\Facades\Jocko;
use Illuminate\Support\Facades\Cache;

abstract class WebsiteSettings
{
    private bool $loaded = false;

    public function __construct()
    {
        $this->loadValues();
    }

    private function loadValues(): self
    {
        if($this->loaded) {
            return $this;
        }

//        if(!Jocko::shouldCache()) {
//            Cache::forget('jocko-settings');
//        }
//
//        $values = Cache::rememberForever('jocko-settings', function () {
//            return Jocko::getSettings();
//        });

        $values = Jocko::getSettings();

        $this->loaded = true;

        foreach($values as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }
}
