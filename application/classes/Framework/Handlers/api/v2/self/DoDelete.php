<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.12.14
 * Time: 14:43
 */

namespace Framework\Handlers\api\v2\self;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\JsonResponse;

class DoDelete implements Controller {

    public function doPost($password, AuthUserModel $user, JsonResponse $response) {

        $user->checkPassword($password);

        $user->delete();

    }

} 