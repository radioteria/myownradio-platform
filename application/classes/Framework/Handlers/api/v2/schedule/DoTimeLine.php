<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 07.05.15
 * Time: 11:56
 */

namespace Framework\Handlers\api\v2\schedule;


use API\REST\TrackCollection;
use Framework\ControllerImpl;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoTimeLine extends ControllerImpl {
    public function doGet(HttpGet $get, TrackCollection $collection, JsonResponse $response) {
        $channel_id = $get->getRequired("channel_id");
        $response->setData($collection->getTimeLineOnChannel($channel_id));
    }
} 