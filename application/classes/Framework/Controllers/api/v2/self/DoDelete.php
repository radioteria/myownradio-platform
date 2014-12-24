<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 24.12.14
 * Time: 11:52
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost(HttpPost $post, AuthUserModel $user, JsonResponse $response) {

        $password = $post->getParameter("password")
            ->getOrElseThrow(ControllerException::noArgument("password"));

        $user->delete($password);

    }

} 