<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\Beans\UserBean;
use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        $user = UserBean::getByFilter("FIND_BY_PARAMS", [ ":key" => "roman@homefs.biz11"])
            ->getOrElseThrow(ControllerException::noEntity("user"));

        $response->setData($user->exportArray());

    }

} 