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
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoOnSelectedChannels implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, TrackCollection $trackCollection) {
        $stream_ids = $get->getRequired("stream_ids");
        $ids_array = explode(",", $stream_ids);
        $response->setData($trackCollection->getPlayingOnChannels($ids_array));
    }
} 