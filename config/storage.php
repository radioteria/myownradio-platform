<?php

return [

    'cache_dir'          => 'storage/cache',

    'session' => [
        'expire_seconds' => 2592000,
        'save_path'      => 'storage/sessions'
    ],

    'images' => [
        'avatars_path'  => 'storage/images/avatars',
        'covers_path'   => 'storage/images/covers'
    ],

    'audio'  => [
        'track_duration_max'    => 14400000,
        'track_file_size_max'   => 536870912,
        'supported_formats'     => 'mp3',
    ],

];
