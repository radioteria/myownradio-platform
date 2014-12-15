<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 14:24
 */

namespace MVC\Controllers\api\v2\self;


use Model\User;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoModifyProfile extends Controller {
    public function doPost(HttpPost $post, User $user, InputValidator $validator) {
        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseThrow(ControllerException::noArgument("info"));
        $email = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $validator->validateEmail($email);

        $user->setName($name)->setInfo($info)->setUserEmail($email);
    }
} 