<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 9:18
 */

namespace MVC\Services;


use Tools\Singleton;

class HttpResponse {
    use Singleton, Injectable;

    public function writeHeader($key, $value) {
        header($key . ":" . $value);
    }

    public function write($data) {
        echo $data;
    }
} 