<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:01
 */

namespace Framework\Controllers\api\v2\channels;


use API\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoAll implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, ChannelsCollection $collection) {
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElseNull();
        $response->setData($collection->getChannelsList($offset, $limit));
    }
} 