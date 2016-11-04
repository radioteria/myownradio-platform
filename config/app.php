<?php

return [

    'debug'     => env('APP_DEBUG', false),

    'timezone'  => 'Europe/Kiev',

    'log_file'  => 'storage/logs/api-server.log',

    'host'      => $_SERVER['HTTP_HOST']

];
