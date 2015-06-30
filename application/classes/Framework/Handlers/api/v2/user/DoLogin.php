<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:50
 */

namespace Framework\Handlers\api\v2\user;


use Framework\Controller;
use Framework\Models\UsersModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\JsonResponse;
use REST\Users;

class DoLogin implements Controller {

    public function doPost(JsonResponse $response, HttpPost $post, UsersModel $users, Users $usersRest) {

        $login      = $post->getOrError("login");
        $password   = $post->getOrError("password");

        $users->logout();

        $userModel = $users->authorizeByLoginPassword($login, $password);

        return $usersRest->getUserByID($userModel->getID(), true);

    }

}