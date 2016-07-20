<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 13.04.2015
 * Time: 13:57
 */
return [
    "application_title" => "MyOwnRadio - your own free web radio station",
    "application_content" => "/var/apps/myownradio.biz/",
    "api" => [
        "streams_per_request_max" => 50,
        "tracks_per_request_max" => 50
    ],
    "media" => [
        "track_extension_pattern" => "mp3|flac|ogg|m4a|ape|aac",
        "track_duration_min" => 15000,
        "track_duration_max" => 14400000,
        "track_size_max" => 536870912,
        "track_preview_command" => "ffmpeg -v quiet -ss %time% -i %path% -filter 'afade=t=in:ss=0:d=2' -vn -acodec libmp3lame -ac 2 -ar 44100 -ab 128k -f mp3 -"
    ]
];