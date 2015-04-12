<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.02.15
 * Time: 14:49
 */

namespace Framework\Handlers\api\check;


use Framework\ControllerImpl;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoStreamPermalink extends ControllerImpl {
    public function doPost(HttpPost $post, InputValidator $validator, AuthUserModel $user, JsonResponse $response) {
        $field = $post->getRequired("field");
        $context = $post->getParameter("context")->getOrElseNull();
        try {
            $validator->validateStreamPermalink($field, $context);
            $response->setData(["available" => true]);
        } catch (ControllerException $ex) {
            $response->setData(["available" => false]);
        }
    }
} 