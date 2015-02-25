<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 20:47
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Tools\JsonPrinter;
use Tools\Singleton;
use Tools\SingletonInterface;

class JsonResponse implements Injectable, SingletonInterface {

    use Singleton;

    private $code = 1;
    private $data = null;
    private $message = "OK";
    private $response = 200;

    public function setData($data) {
        $this->data = $data;
    }

    public function setMessage($message) {
        $this->message = strval($message);
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setResponseCode($code) {
        $this->response = $code;
    }

    private function write() {

        ob_start("ob_gzhandler");

        http_response_code($this->response);

        header("Content-Type: application/json");

        $printer = new JsonPrinter();

        $printer->printJSON([
            "code" => $this->code,
            "message" => $this->message,
            "data" => $this->data
        ]);

    }

}