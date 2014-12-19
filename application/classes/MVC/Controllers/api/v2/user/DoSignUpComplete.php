<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 10:19
 */

namespace MVC\Controllers\api\v2\self;


use Model\UsersModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoSignUpComplete extends Controller {

    public function doPost(HttpPost $post, InputValidator $validator) {

        $code       = $post->getParameter("code")       ->getOrElseThrow(ControllerException::noArgument("code"));
        $login      = $post->getParameter("login")      ->getOrElseThrow(ControllerException::noArgument("login"));
        $password   = $post->getParameter("password")   ->getOrElseThrow(ControllerException::noArgument("password"));
        $name       = $post->getParameter("name")       ->getOrElseNull();
        $info       = $post->getParameter("info")       ->getOrElseNull();
        $permalink  = $post->getParameter("permalink")  ->getOrElseNull();

        $validator->validateRegistrationCode($code);
        $validator->validatePassword($password);
        $validator->validateLogin($login);
        $validator->validateUserPermalink($permalink);

        UsersModel::completeRegistration($code, $login, $password, $name, $info, $permalink);

    }

} 