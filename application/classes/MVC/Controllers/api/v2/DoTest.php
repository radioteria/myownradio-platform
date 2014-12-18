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
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        $result = [];

        $user = new UserBean();

        $user->setLogin("megalogin");
        $user->setInfo("User info");
        $user->setLastVisitDate(time());
        $user->setName("MegaBrain");
        $user->setEmail("test@mail.com");
        $user->setRegistrationDate(time());
        $user->setPassword(md5("abc"));

        $result[] = $user->exportArray();

        $user->beanSave();

        $result[] = $user->exportArray();

        $user->beanDelete();

        $result[] = $user->exportArray();

        $response->setData($result);

    }

} 