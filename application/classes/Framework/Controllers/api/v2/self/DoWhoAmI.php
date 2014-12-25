<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.12.14
 * Time: 16:14
 */

namespace Framework\Controllers\api\v2\self;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\JsonResponse;
use REST\Users;

class DoWhoami implements Controller {

    public function doGet(AuthUserModel $userModel, JsonResponse $response) {
        $user = Users::getInstance()->getUserByID($userModel->getID());
        $response->setData($user);
    }

} 