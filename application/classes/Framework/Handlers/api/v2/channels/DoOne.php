<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 07.04.15
 * Time: 13:32
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use API\REST\UserCollection;
use Framework\Controller;

class DoOne implements Controller {
    public function doGet($stream_id, ChannelsCollection $collection, UserCollection $userCollection) {
        $channel_data   = $collection->getOneChannel($stream_id);
        $user_data      = $userCollection->getSingleUser($channel_data["uid"]);

        return [
            "channel"   => $channel_data,
            "owner"     => $user_data
        ];
    }
} 