<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:53
 */

namespace Framework\Controllers\api\v2\user;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpPost;
use Framework\Services\InputValidator;
use Model\UsersModel;

class DoPasswordResetComplete implements Controller {

    public function doPost(HttpPost $post, InputValidator $validator) {

        $code       = $post->getParameter("code")     ->getOrElseThrow(ControllerException::noArgument("code"));
        $password   = $post->getParameter("password") ->getOrElseThrow(ControllerException::noArgument("password"));

        $validator->validatePassword($password);

        UsersModel::completePasswordReset($code, $password);

    }
} 