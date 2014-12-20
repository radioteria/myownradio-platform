<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 20:47
 */

namespace Framework\Services;


use Tools\Singleton;

class JsonResponse {

    use Singleton, Injectable;

    private $code = 1;
    private $data = null;
    private $message = "OK";
    private $response = 200;
    private $enabled = true;

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

        http_response_code($this->response);

        if ($this->enabled) {

            header("Content-Type: application/json");

            echo json_encode([
                "code" => $this->code,
                "message" => $this->message,
                "data" => $this->data
            ]);

        }

    }

    public function disable() {
        $this->enabled = false;
    }

}