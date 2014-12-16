<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\User;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {
    public function doGet(JsonResponse $response) {
        $user = new User(73);
        $response->setData($user->getActivePlan()->getName());
    }
} 