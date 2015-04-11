<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.04.15
 * Time: 9:47
 */

namespace Framework\Controllers\api\v3\tracks;


use API\REST\TrackCollection;
use Framework\Controller;
use Framework\ControllerImpl;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoChannel extends ControllerImpl {
    public function doGet(HttpGet $get, JsonResponse $response, TrackCollection $trackCollection) {
        $channel_id = $get->getRequired("stream_id", FILTER_VALIDATE_INT);
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElseZero();
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElse(TrackCollection::TRACKS_PER_REQUEST_MAX);
        $response->setData($trackCollection->getTracksFromChannel($channel_id, $offset, $limit));
    }
} 