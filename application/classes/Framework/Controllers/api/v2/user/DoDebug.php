<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:50
 */

namespace Framework\Controllers\api\v2\user;


use Framework\Controller;
use Framework\Models\UsersModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoDebug implements Controller {

    public function doPost(HttpPost $post, UsersModel $users, JsonResponse $response) {

        $id = intval($post->getRequired("user_id"));

        $users->logout();
        $users->authorizeById($id);

    }

}