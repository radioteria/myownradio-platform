<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:16
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;

class DoSuggest implements Controller {
    public function doGet($query, ChannelsCollection $collection) {
        return $collection->getChannelsSuggestion($query);
    }
} 