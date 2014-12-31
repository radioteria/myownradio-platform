<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:02
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use REST\Users;

class DoSelf implements Controller {

    public function doGet(AuthUserModel $userModel, JsonResponse $response) {

        $user = Users::getInstance()->getUserByID($userModel->getID());
        $response->setData($user);
        
    }

    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $name = $post->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));
        $info = $post->getParameter("info")->getOrElseEmpty();
        $permalink = $post->getParameter("permalink")->getOrElseNull();

        $user->edit($name, $info, $permalink);

    }

} 