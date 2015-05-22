<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 03.01.15
 * Time: 00:03
 */

namespace Framework\Handlers\api\exists;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoLogin extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, TestFields $test) {
        $field = $post->getRequired("field");
        $response->setData([
            "exists" => $test->testLogin($field)
        ]);
    }
} 