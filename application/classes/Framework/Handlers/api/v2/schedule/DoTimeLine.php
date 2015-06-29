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
use Framework\Defaults;

class DoTimeLine extends ControllerImpl {
    public function doGet($channel_id, TrackCollection $collection) {
        return $collection->getTimeLineOnChannel($channel_id, -100000, Defaults::TIMELINE_WIDTH);
    }
} 