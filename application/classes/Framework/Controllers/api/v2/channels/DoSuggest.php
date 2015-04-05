<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:16
 */

namespace Framework\Controllers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;

class DoSuggest implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, JsonResponse $response) {
        $filter = $get->getRequired("query");
        $response->setData($collection->getChannelsSuggestion($filter));
    }
} 