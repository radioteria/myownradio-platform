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
use Framework\Services\JsonResponse;
use Framework\Services\ValidatorTemplates;

class DoUserPermalink extends ControllerImpl {
    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {
        $field = $post->getRequired("field");
        try {
            ValidatorTemplates::validateUserPermalink($field, $user->getID());
            $response->setData(["available" => true]);
        } catch (ControllerException $ex) {
            $response->setData(["available" => false]);
        }
    }
} 