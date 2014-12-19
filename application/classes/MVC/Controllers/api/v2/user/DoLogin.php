<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:50
 */

namespace MVC\Controllers\api\v2\user;


use Model\UsersModel;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpPost;

class DoLogin extends Controller {
    public function doPost(HttpPost $post) {
        $login      = $post->getParameter("login")->getOrElseThrow(ControllerException::noArgument("login"));
        $password   = $post->getParameter("password")->getOrElseThrow(ControllerException::noArgument("password"));
        UsersModel::authorizeByLoginPassword($login, $password);
    }

} 