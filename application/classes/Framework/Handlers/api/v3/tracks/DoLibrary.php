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
use Framework\Services\Http\HttpGet;
use Framework\Services\JsonResponse;
use Tools\Optional\Filter;

class DoLibrary extends ControllerImpl {
    public function doGet(HttpGet $get, JsonResponse $response, TrackCollection $trackCollection) {
        $offset     = $get->get("offset")->filter(Filter::isNumber())->orZero();
        $limit      = $get->get("limit")->filter(Filter::isNumber())
                          ->getOrElse(TrackCollection::TRACKS_PER_REQUEST_MAX);
        $response->setData($trackCollection->getTracksFromLibrary($offset, $limit));
    }
} 