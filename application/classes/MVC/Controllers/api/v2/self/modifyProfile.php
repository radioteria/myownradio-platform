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

class modifyProfile extends Controller {
    public function doPost(HttpPost $post, User $user, InputValidator $validator) {
        $rawName = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $rawInfo = $post->getParameter("info")->getOrElseThrow(ControllerException::noArgument("info"));
        $rawEmail = $post->getParameter("email")->getOrElseThrow(ControllerException::noArgument("email"));

        $verifiedEmail = $validator->validateEmail($rawEmail);

        $user->setName($rawName)->setInfo($rawInfo)->setUserEmail($rawEmail)->update();
    }
} 