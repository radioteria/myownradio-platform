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
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Tools\Common;

class DoUpcoming extends ControllerImpl {
    public function doGet(HttpGet $get, JsonResponse $response, ChannelsCollection $collection) {
        $channels = $get->getParameter("channels")->getOrElseNull();
        $response->setData($collection->getUpcomingChange(Common::split(",", $channels)));
    }
} 