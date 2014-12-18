<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\ActiveRecords\UserAR;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        /** @var UserAR $user */
        $user = UserAR::getByID(1)->getOrElseNull();

        $response->setData($user->exportArray());

    }

} 