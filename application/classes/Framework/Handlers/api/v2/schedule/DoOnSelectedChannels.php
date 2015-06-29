<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 07.04.15
 * Time: 16:34
 */

namespace Framework\Handlers\api\v2\schedule;


use API\REST\TrackCollection;
use Framework\Controller;

class DoOnSelectedChannels implements Controller {
    public function doGet($stream_ids, TrackCollection $trackCollection) {
        $ids_array = explode(",", $stream_ids);
        return $trackCollection->getPlayingOnChannels($ids_array);
    }
} 