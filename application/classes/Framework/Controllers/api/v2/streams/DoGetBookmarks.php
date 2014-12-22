<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 11:36
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Services\JsonResponse;
use Model\AuthUserModel;
use REST\Streams;

class DoGetBookmarks implements Controller {

    public function doGet(JsonResponse $response, Streams $streams, AuthUserModel $user) {

        $response->setData($streams->getBookmarksByUser($user));

    }

} 