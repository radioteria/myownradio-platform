<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 10.04.15
 * Time: 9:47
 */

namespace Framework\Handlers\api\v3\tracks;


use API\REST\TrackCollection;
use Framework\ControllerImpl;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoChannel extends ControllerImpl {
    public function doGet(HttpGet $get, TrackCollection $trackCollection) {
        $channel_id = $get->getFiltered("stream_id", Filter::$isValidId);
        $offset     = $get->getFiltered("offset", Filter::$isNumber);
        $limit      = $get->get("limit")->filter(Filter::$isNumber)
                          ->getOrElse(TrackCollection::TRACKS_PER_REQUEST_MAX);

        return $trackCollection->getTracksFromChannel($channel_id, $offset, $limit);
    }
} 