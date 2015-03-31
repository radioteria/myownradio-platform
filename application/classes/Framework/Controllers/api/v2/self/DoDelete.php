<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:43
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $password = $post->getRequired("password");

        $user->checkPassword($password);

        $user->delete();

    }

} 