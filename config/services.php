<?php

/*
|--------------------------------------------------------------------------
| Myownradio Services
|--------------------------------------------------------------------------
|
| Here is the configuration for services used by myownradio backend.
|
*/

return [

    'facebook' => [
        'app_id'        => env('FACEBOOK_APP_ID'),
        'app_secret'    => env('FACEBOOK_APP_SECRET')
    ],

    's3' => [
        'access_key'        => env('S3_ACCESS_KEY'),
        'secret_key'        => env('S3_SECRET_KEY'),
        'bucket'            => env('S3_BUCKET', 'myownradio-storage'),
        'region'            => env('S3_REGION', 'eu-central-1'),
        'signature_version' => 'v4',
    ],

    'ffmpeg' => [
        'preview_args' => '-ss %f -i %s -filter \'afade=t=in:ss=0:d=2\' -acodec libmp3lame -ac 2 -ab 128k -f mp3 -'
    ],

    'mediainfo_cmd' => env('MEDIAINFO_PATH', 'mediainfo'),
    'ffmpeg_cmd'    => env('FFMPEG_PATH', 'ffmpeg'),

];
