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
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoChangePassword implements Controller {
    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $password = $post->getParameter("password")
            ->getOrElseThrow(ControllerException::noArgument("password"));

        $user->changePassword($password);

    }
} 