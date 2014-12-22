<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 14:02
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;

class DoChangePassword implements Controller {
    public function doPost(HttpPost $post, InputValidator $validator, AuthUserModel $user, JsonResponse $response) {

        $password = $post->getParameter("password")
            ->getOrElseThrow(ControllerException::noArgument("password"));

        $validator->validatePassword($password);

        $user->changePassword($password);

    }
} 