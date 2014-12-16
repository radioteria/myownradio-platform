<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\AuthorizedUser;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {
    public function doGet(AuthorizedUser $user, JsonResponse $response) {
        $response->setData(date("M d, Y", $user->getActivePlanExpire()));
    }
} 