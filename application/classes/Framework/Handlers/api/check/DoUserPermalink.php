<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 15:24
 */

namespace Framework\Handlers\api\check;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoUserPermalink extends ControllerImpl {
    public function doPost(HttpPost $post, InputValidator $validator, AuthUserModel $user, JsonResponse $response) {
        $field = $post->getRequired("field");
        try {
            $validator->validateUserPermalink($field, $user->getID());
            $response->setData(["available" => true]);
        } catch (ControllerException $ex) {
            $response->setData(["available" => false]);
        }
    }
} 