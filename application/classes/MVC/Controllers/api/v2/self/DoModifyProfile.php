<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 14:24
 */

namespace MVC\Controllers\api\v2\self;


use Model\AuthUserModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;
use MVC\Services\InputValidator;

class DoModifyProfile extends Controller {
    public function doPost(HttpPost $post, AuthUserModel $user) {

        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseThrow(ControllerException::noArgument("info"));
        $email = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $user->edit($name, $info, $email);

    }
} 