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
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoOnSingleChannel implements Controller {
    public function doGet(HttpGet $get, TrackCollection $trackCollection) {
        $stream_id = $get->getFiltered("stream_id", Filter::$isNumber);
        return $trackCollection->getPlayingOnChannel($stream_id);
    }
} 