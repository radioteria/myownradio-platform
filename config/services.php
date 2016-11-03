<?php

/*
|--------------------------------------------------------------------------
| Myownradio Services
|--------------------------------------------------------------------------
|
| Here is the configuration for services that myownradio api uses.
|
*/

return [

    'facebook' => [
        'app_id'        => env('FACEBOOK_APP_ID'),
        'app_secret'    => env('FACEBOOK_APP_SECRET')
    ],

    's3' => [
        'access_key'    => env('S3_ACCESS_KEY'),
        'secret_key'    => env('S3_SECRET_KEY'),
        'bucket'        => env('S3_BUCKET', 'myownradio-service'),
        'region'        => env('S3_REGION', 'eu-central-1'),
    ]

];
