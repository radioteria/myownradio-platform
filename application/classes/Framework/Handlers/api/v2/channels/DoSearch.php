<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:56
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoSearch implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection) {
        $filter = $get->getOrError("query");
        $offset = $get->get("offset")->filter(Filter::isNumber())
                      ->orZero();
        $limit  = $get->get("limit")->filter(Filter::isNumber())
                      ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        return [
            "query" => $filter,
            "channels" => $collection->getChannelsListBySearch($filter, $offset, $limit)
        ];
    }
} 