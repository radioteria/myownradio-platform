<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:00
 */

namespace Framework\Controllers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoTag implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, JsonResponse $response) {
        $filter = $get->getRequired("query");
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElseNull();

        $response->setData([
            "query" => $filter,
            "channels" => $collection->getChannelsListByTag($filter, $offset, $limit)
        ]);
    }
} 