<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:36
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;
use Model\UserModel;
use REST\Streams;

class DoGetBookmarks implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, HttpGet $get) {

        /** @var UserModel $user */

        $user = $get->getParameter("user_id")
            ->then(function (&$arg) { $arg = UserModel::getInstance($arg); })
            ->otherwise(function (&$arg) { $arg = AuthUserModel::getInstance(); })
            ->get();

        $response->setData($streams->getBookmarksByUser($user));

    }

} 