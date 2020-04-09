<?php

return [

    'cache_dir'          => '../storage/cache',

    'session' => [
        'expire_seconds' => 2592000,
        'save_path'      => '/var/lib/php/sessions'
    ],

    'audio'  => [
        'track_duration_max'    => 14400000,
        'track_file_size_max'   => 536870912,
        'supported_formats'     => 'mp3|flac|aac|ogg|m4a|wav',
    ],

    'local' => [
        'dir'                   => $_ENV['BACKEND_STORAGE_LOCAL_DIR']
    ]

];
