<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.05.15
 * Time: 14:42
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\ControllerImpl;
use Framework\Services\JsonResponse;

class DoRandom extends ControllerImpl {
    public function doGet(ChannelsCollection $collection, JsonResponse $response) {
        $response->setData($collection->getRandomChannel());
    }
} 