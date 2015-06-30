<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 13:40
 */

namespace Tools\Optional;


use Framework\Services\JsonResponse;

class Consumer {
    /**
     * @return \Closure
     */
    public static function write() {
        return function ($value) { echo $value; };
    }

    /**
     * @return \Closure
     */
    public static function json() {
        $response = JsonResponse::getInstance();
        return function ($data) use (&$response) {
            $response->setData($data);
        };
    }
}