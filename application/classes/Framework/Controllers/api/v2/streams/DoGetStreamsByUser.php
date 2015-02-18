<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:52
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Models\UserModel;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetStreamsByUser implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get) {


        $user = $get->getRequired("user");

        $response->setData($streams->getByUser($user));



    }

} 