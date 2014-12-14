<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 14:02
 */

namespace MVC\Controllers\api\v2\self;


use Model\User;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class changePassword extends Controller {
    public function doPost(HttpPost $post, InputValidator $validator, User $user) {
        $rawPassword = $post->getParameter("password")
            ->getOrElseThrow(ControllerException::noArgument("password"));
        $validPassword = $validator->ValidatePassword($rawPassword);
        $user->changePassword($validPassword);
    }
} 