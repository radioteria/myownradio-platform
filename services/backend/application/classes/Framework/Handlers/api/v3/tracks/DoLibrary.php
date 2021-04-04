<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.04.15
 * Time: 9:43
 */

namespace Framework\Handlers\api\v3\tracks;


use API\REST\TrackCollection;
use Framework\ControllerImpl;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoLibrary extends ControllerImpl {
    public function doGet(HttpGet $get, JsonResponse $response, TrackCollection $trackCollection) {
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElseZero();
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElse(TrackCollection::TRACKS_PER_REQUEST_MAX);
        $response->setData($trackCollection->getTracksFromLibrary($offset, $limit));
    }
} 