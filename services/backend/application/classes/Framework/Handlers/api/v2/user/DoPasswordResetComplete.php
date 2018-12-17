<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:53
 */

namespace Framework\Handlers\api\v2\user;


use Framework\Controller;
use Framework\Models\UsersModel;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Framework\Services\JsonResponse;

class DoPasswordResetComplete implements Controller {

    public function doPost(HttpPost $post, InputValidator $validator, UsersModel $users, JsonResponse $response) {

        $code = $post->getRequired("code");
        $password = $post->getRequired("password");

        $validator->validatePassword($password);

        $users->completePasswordReset($code, $password);

    }
} 