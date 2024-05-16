<?php

// config for Code16/OzuClient
return [
    'api_host' => env('OZU_API_HOST', 'https://ozu.code16.fr/'),
    'api_key' => env('OZU_API_KEY'),
    'api_version' => env('OZU_API_VERSION', 'v1'),
    'cdn_url' => env('OZU_CDN_URL'),
    'website_key' => env('OZU_WEBSITE_KEY'),
    'collections' => [],
];
