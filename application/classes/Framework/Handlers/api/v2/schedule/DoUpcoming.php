<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.04.15
 * Time: 15:18
 */

namespace Framework\Handlers\api\v2\schedule;


use API\REST\ChannelsCollection;
use Framework\ControllerImpl;
use Tools\Common;
use Tools\Optional\Option;

class DoUpcoming extends ControllerImpl {
    public function doGet(Option $channels, ChannelsCollection $collection) {
        return $collection->getUpcomingChange(Common::split(",", $channels->orNull()));
    }
} 