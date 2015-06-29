<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 21:02
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoBookmarks implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection) {
        $offset = $get->get("offset")->filter(Filter::$isNumber)
                      ->orZero();
        $limit = $get->get("limit")->filter(Filter::$isNumber)
                      ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        return ["channels" => $collection->getBookmarkedChannels($offset, $limit)];
    }
} 