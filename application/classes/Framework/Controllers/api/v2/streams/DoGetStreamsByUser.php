<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:52
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;
use Model\UserModel;
use REST\Streams;

class DoGetStreamsByUser implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get) {

        $get->getParameter("user_id")
            ->then(function ($id) use ($response, $streams) {
                $response->setData($streams->getByUser(UserModel::getInstance($id)));
            })
            ->otherwise(function () use ($response, $streams) {
                $response->setData($streams->getByUser(AuthUserModel::getInstance()));
            });

    }

} 