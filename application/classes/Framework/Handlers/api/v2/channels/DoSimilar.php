<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 08.04.15
 * Time: 12:09
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoSimilar implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, ChannelsCollection $collection) {
        $stream_id = $get->getRequired("stream_id");
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);
        $response->setData([
            "channels" => $collection->getSimilarChannels($stream_id, $offset, $limit)
        ]);
    }
} 