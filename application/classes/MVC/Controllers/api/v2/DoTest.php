<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\ActiveRecords\User;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        /** @var User $user */
        $user = User::getByID(1)->getOrElseNull();

        $response->setData($user->exportArray());

    }

} 