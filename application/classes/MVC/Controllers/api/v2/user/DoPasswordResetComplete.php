<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 13:53
 */

namespace MVC\Controllers\api\v2\user;


use Model\Users;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoPasswordResetComplete extends Controller {

    public function doPost(HttpPost $post, InputValidator $validator) {

        $code       = $post->getParameter("code")     ->getOrElseThrow(ControllerException::noArgument("code"));
        $password   = $post->getParameter("password") ->getOrElseThrow(ControllerException::noArgument("password"));

        $validator->validatePassword($password);

        Users::completePasswordReset($code, $password);

    }
} 