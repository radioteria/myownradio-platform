<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 14:24
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;

class DoModifyProfile implements Controller {
    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseThrow(ControllerException::noArgument("info"));
        $email = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $user->edit($name, $info, $email);

    }
} 