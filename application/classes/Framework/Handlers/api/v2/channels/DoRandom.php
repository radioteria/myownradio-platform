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

class DoRandom extends ControllerImpl {
    public function doGet(ChannelsCollection $collection) {
        return $collection->getRandomChannel();
    }
} 