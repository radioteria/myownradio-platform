<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 07.04.15
 * Time: 16:31
 */

namespace Framework\Handlers\api\v2\schedule;


use API\REST\TrackCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoOnSingleChannel implements Controller {
    public function doGet(HttpGet $get, JsonResponse $response, TrackCollection $trackCollection) {
        $stream_id = $get->getRequired("stream_id", FILTER_SANITIZE_NUMBER_INT);
        $response->setData($trackCollection->getPlayingOnChannel($stream_id));
    }
} 