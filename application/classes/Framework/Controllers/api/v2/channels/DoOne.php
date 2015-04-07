<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 07.04.15
 * Time: 13:32
 */

namespace Framework\Controllers\api\v2\channels;


use API\REST\ChannelsCollection;
use API\REST\UserCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoOne implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, UserCollection $userCollection, JsonResponse $response) {
        $stream_id = $get->getRequired("stream_id");
        $channel_data = $collection->getOneChannel($stream_id);
        $user_data = $userCollection->getSingleUser($channel_data["uid"]);
        $response->setData([
            "channel"   => $channel_data,
            "owner"     => $user_data
        ]);
    }
} 