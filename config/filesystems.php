<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // 's3' => [
        //     'driver' => 's3',
        //     'key' => '00dc8b12460c74f87c392b5769643dd9',
        //     'secret' => '5b60fad56385dbc741fda39c2309a597d9847785323d5606a88ad95bb89df601',
        //     'region' => 'auto',
        //     'bucket' => 'fls-a1b45d72-144f-4d25-b368-dbbbfbeb7cbb',
        //     'url' => 'https://fls-a1b45d72-144f-4d25-b368-dbbbfbeb7cbb.laravel.cloud',
        //     'endpoint' => 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com',
        //     'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        //     'throw' => false,
        //     'report' => false,
        // ],

        // 'storage' => [
        //     'driver' => 's3',
        //     'AWS_BUCKET'=> 'fls-a1b45d72-144f-4d25-b368-dbbbfbeb7cbb',
        //     'AWS_DEFAULT_REGION'=> 'auto',
        //     'AWS_ENDPOINT'=> 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com',
        //     'AWS_URL'=> 'https://fls-a1b45d72-144f-4d25-b368-dbbbfbeb7cbb.laravel.cloud',
        //     'AWS_USE_PATH_STYLE_ENDPOINT'=> false
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
