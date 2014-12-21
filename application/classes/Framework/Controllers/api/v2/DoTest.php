<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Services\JsonResponse;
use Objects\User;

class DoTest implements Controller {

    public function doGet(JsonResponse $response) {
        $users = User::getList();
        $response->setData($users);
    }

} 