<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 18:53
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoPopular implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, ChannelsCollection $collection) {
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);
        $response->setData([
            "channels" => $collection->getChannelsPopular($offset, $limit)
        ]);
    }
} 