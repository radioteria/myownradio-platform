<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.01.15
 * Time: 18:25
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoLogin extends ControllerImpl {
    public function doPost(HttpPost $post, JsonResponse $response, TestFields $test) {

        $field = $post->getRequired("field");

        $response->setData(["available" => !$test->testLogin($field)]);

    }
} 