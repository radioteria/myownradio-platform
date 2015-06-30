<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:02
 */

namespace Framework\Handlers\api\v2;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Models\UsersModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\Http\HttpPut;
use Framework\Services\JsonResponse;
use REST\Streams;
use REST\Users;
use Tools\Optional\Transform;

class DoSelf implements Controller {

    public function doGet(AuthUserModel $userModel, Streams $streams, Users $users) {

        return array(
            'user' => $users->getUserByID($userModel->getID(), true),
            'streams' => $streams->getByUser($userModel->getID()),
            'client_id' => $userModel->getClientId()
        );

    }

    public function doPut(HttpPut $put, UsersModel $users, JsonResponse $response) {

        $login = $put->getOrError("login");
        $password = $put->getOrError("password");
        $remember = $put->get("remember")->map(Transform::toBoolean())->orFalse();

        $users->logout();
        $users->authorizeByLoginPassword($login, $password, $remember);

        return AuthUserModel::getInstance()->getToken();

    }

    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $name = $post->get("name")->orEmpty();
        $info = $post->get("info")->orEmpty();
        $permalink = $post->get("permalink")->orNull();
        $countryId = $post->get("country_id")->orNull();

        $user->edit($name, $info, $permalink, $countryId);

    }

    public function doDelete(UsersModel $users, JsonResponse $response) {

        $users->logout();

    }

} 