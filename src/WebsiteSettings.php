<?php

namespace Code16\JockoClient;

use Illuminate\Support\Facades\Cache;
use Code16\JockoClient\Facades\JockoClient;

class WebsiteSettings
{
    public function __construct()
    {
        if(Cache::has('jocko-settings')) {
            $settings = Cache::get('jocko-settings');
        } else {
            $settings = JockoClient::getSettings();
            Cache::put('jocko-settings', $settings, 60);
        }

        foreach($settings as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
