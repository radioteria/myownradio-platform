<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 08.04.15
 * Time: 12:09
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoSimilar implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection) {
        $stream_id  = $get->getOrError("stream_id");
        $offset     = $get->get("offset")->filter(Filter::$isNumber)
                          ->orZero();
        $limit      = $get->get("limit")->filter(Filter::$isNumber)
                          ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        return [
            "channels" => $collection->getSimilarChannels($stream_id, $offset, $limit)
        ];
    }
} 