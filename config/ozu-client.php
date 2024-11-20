<?php

use Code16\OzuClient\OzuCms\Storage\OzuCustomFTPStorage;
use Code16\OzuClient\OzuCms\Storage\OzuCustomS3Storage;

return [
    // List here your collections, ie the Models that will be handled by the Ozu CMS.
    // You must also properly configure them (see documentation).
    'collections' => [
        // \App\Models\Project::class,
    ],

    // The Ozu unique key of the website to use
    'website_key' => env('OZU_WEBSITE_KEY'),


    // If you want to use custom storage for your files, you can configure it here.
    // If you change your configuration after having uploaded files, you will have to manually move them to the new storage
    // Or reset them in ozu.
    // This is an example of how to configure a custom S3 storage:

   /* 'custom_storage' => OzuCustomS3Storage::make()
        ->setBucketName(env('OZU_S3_BUCKET'))
        ->setRegion(env('OZU_S3_REGION'))
        ->setKey(env('OZU_S3_KEY'))
        ->setSecret(env('OZU_S3_SECRET'))
        ->setEndpoint(env('OZU_S3_ENDPOINT'))
        ->setUsePathStyleEndpoint(env('OZU_S3_USE_PATH_STYLE_ENDPOINT'))
    ,*/

    // This is an example of how to configure a custom FTP storage:
    /*'custom_storage' => OzuCustomFTPStorage::make()
        ->setHost(env('OZU_FTP_HOST'))
        ->setPort(env('OZU_FTP_PORT'))
        ->setUsername(env('OZU_FTP_USERNAME'))
        ->setPassword(env('OZU_FTP_PASSWORD'))
        ->setRootPath(env('OZU_FTP_ROOT_PATH'))
     ,*/

    // The API key to use to authenticate with the Ozu API
    // (do not fill this value here, use the .env file instead)
    'api_key' => env('OZU_API_KEY'),

    // The following values should not be modified unless you know what you are doing
    'api_host' => env('OZU_API_HOST', 'https://ozu.code16.fr/'),
    'api_version' => env('OZU_API_VERSION', 'v1'),

    'cdn_url' => env('OZU_CDN_URL'),
];
