<?php

return [

    'session' => [
        'expire_seconds' => 2592000,
        'save_path'      => 'storage/sessions'
    ],

    'images' => [
        'avatars_path'  => 'storage/images/avatars',
        'covers_path'   => 'storage/images/covers'
    ],

    'audio'  => [
        'maximal_length'    => 14400000,
        'supported_formats' => 'mp3'
    ]

];
