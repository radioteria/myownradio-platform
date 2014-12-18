<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\Beans\StreamBean;
use Model\Beans\UserBean;
use MVC\Controller;
use MVC\Services\JsonResponse;

class DoTest extends Controller {

    public function doGet(JsonResponse $response) {

        $user = new UserBean();

        $user->setLogin("megalogin");
        $user->setInfo("User info");
        $user->setLastVisitDate(time());
        $user->setName("MegaBrain");
        $user->setMail("test@mail.com");
        $user->setRegistrationDate(time());
        $user->setPassword(md5("abc"));

        $response->setData($user->exportArray());

        $id = $user->beanSave();

        $response->setMessage("NEW USER ID=" . $id);

    }

} 