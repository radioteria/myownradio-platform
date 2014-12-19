<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 14:02
 */

namespace MVC\Controllers\api\v2\self;


use Model\AuthUserModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoChangePassword extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator, AuthUserModel $user) {

        $password = $post->getParameter("password")
            ->getOrElseThrow(ControllerException::noArgument("password"));

        $validator->validatePassword($password);

        $user->changePassword($password);

    }
} 