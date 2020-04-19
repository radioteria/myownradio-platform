<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:50
 */

namespace Framework\Handlers\api\v2\user;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\UsersModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use REST\Users;

class DoLogin implements Controller {

    public function doPost(HttpPost $post, UsersModel $users, JsonResponse $response, Users $usersRest) {

        $login = $post->getParameter("login")->getOrElseThrow(ControllerException::noArgument("login"));
        $password = $post->getParameter("password")->getOrElseThrow(ControllerException::noArgument("password"));
        $remember = boolval($post->getParameter("remember")->getOrElseFalse());

        $users->logout();

        $userModel = $users->authorizeByLoginPassword($login, $password);

        $response->setData($usersRest->getUserByID($userModel->getID(), true));

    }

}