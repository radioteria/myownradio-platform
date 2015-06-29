<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 15:15
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Models\AuthUserModel;
use Framework\Services\Http\HttpGet;
use Tools\Optional\Filter;

class DoMy implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, AuthUserModel $model) {
        $offset = $get->get("offset")->filter(Filter::$isNumber)
            ->orZero();
        $limit = $get->get("limit")->filter(Filter::$isNumber)
            ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        return [
            "user" => $model->toRestFormat(),
            "channels" => $collection->getChannelsListBySelf($offset, $limit)
        ];
    }
} 