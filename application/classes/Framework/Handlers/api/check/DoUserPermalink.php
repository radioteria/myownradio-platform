<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 15:24
 */

namespace Framework\Handlers\api\check;


use Business\Test\TestFields;
use Framework\ControllerImpl;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoUserPermalink extends ControllerImpl {
    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response, TestFields $test) {

        $field = $post->getRequired("field");

        $result = $test->testUserPermalink($field);

        $response->setData(["available" => $result === false || $result == $user->getID()]);

    }
} 