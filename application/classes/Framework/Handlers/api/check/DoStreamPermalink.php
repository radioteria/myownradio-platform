<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.02.15
 * Time: 14:49
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoStreamPermalink extends ControllerImpl {
    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response, TestFields $test) {

        $field = $post->getRequired("field");
        $context = $post->getParameter("context")->getOrElseNull();

        $result = $test->testStreamPermalink($field);

        $response->setData(["available" => $result === false || $result == $context]);

    }
} 