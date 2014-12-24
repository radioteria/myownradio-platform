<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:36
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Models\UserModel;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetBookmarks implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get) {

        $get->getParameter("user_id")
            ->then(function ($id) use ($response, $streams) {
                $response->setData($streams->getBookmarksByUser(UserModel::getInstance($id)));
            })
            ->otherwise(function () use ($response, $streams) {
                $response->setData($streams->getBookmarksByUser(AuthUserModel::getInstance()));
            });

    }

} 