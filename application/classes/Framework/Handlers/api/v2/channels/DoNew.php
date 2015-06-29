<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.04.15
 * Time: 13:08
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\ControllerImpl;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoNew extends ControllerImpl {
    public function doGet(HttpGet $get, ChannelsCollection $collection) {
        $offset = $get->get("offset")->filter(Filter::$isNumber)
                      ->orZero();
        $limit  = $get->get("limit")->filter(Filter::$isNumber)
                      ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        return [
            "channels" => $collection->getNewChannelsList($offset, $limit)
        ];
    }
} 