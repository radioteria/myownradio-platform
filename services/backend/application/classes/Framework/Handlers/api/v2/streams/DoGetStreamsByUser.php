<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:52
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;
use REST\Users;

class DoGetStreamsByUser implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get, Users $users) {


        $user = $get->getRequired("user");

        $response->setData([
            "user" => $users->getUserByID($user),
            "streams" => $streams->getByUser($user)
        ]);



    }

} 