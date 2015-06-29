<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 10:19
 */

namespace Framework\Handlers\api\v2\user;


use Framework\Controller;
use Framework\Models\UsersModel;
use Framework\Services\Http\HttpPost;
use Framework\Services\JsonResponse;

class DoSignUpComplete implements Controller {

    public function doPost(HttpPost $post, UsersModel $users, JsonResponse $response) {

        $code       = $post->getOrError("code");
        $login      = $post->getOrError("login");
        $password   = $post->getOrError("password");
        $name       = $post->get("name")->orNull();
        $info       = $post->get("info")->orNull();
        $permalink  = $post->get("permalink")->orNull();
        $countryId  = $post->get("country_id")->orNull();

        $users->completeRegistration($code, $login, $password, $name, $info, $permalink, $countryId);

    }

} 