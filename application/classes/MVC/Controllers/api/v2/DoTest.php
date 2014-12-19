<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 22:55
 */

namespace MVC\Controllers\api\v2;


use Model\ActiveRecords\User;
use Model\AuthUserModel;
use Model\UserModel;
use Model\Users;
use MVC\Controller;
use MVC\Services\JsonResponse;
use Tools\String;

class DoTest extends Controller {

    public function doGet() {


        $string = new String("Hello, World! Давай, до свиданья!");

        echo $string;

        die();

    }

} 