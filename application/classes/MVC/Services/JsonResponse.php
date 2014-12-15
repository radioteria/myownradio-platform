<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 20:47
 */

namespace MVC\Services;


use Tools\Singleton;

class JsonResponse {
    use Singleton, Injectable;

    private $data = null;
    private $message = "OK";

    public function setData($data) {
        $this->data = $data;
    }

    public function setMessage($message) {
        $this->message = $message;
    }
}