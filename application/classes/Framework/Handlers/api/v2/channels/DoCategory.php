<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:18
 */

namespace Framework\Handlers\api\v2\channels;


use API\REST\ChannelsCollection;
use Framework\Controller;
use Framework\Services\Http\HttpGet;
use Framework\Services\Validators\CategoryValidator;
use Tools\Optional\Filter;

class DoCategory implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, CategoryValidator $validator) {
        $category_name  = $get->getOrError("category_name");
        $offset         = $get->get("offset")->filter(Filter::$isNumber)
                              ->orZero();
        $limit          = $get->get("limit")->filter(Filter::$isNumber)
                              ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        // todo: update category validator
        $category = $validator->validateChannelCategoryByPermalink($category_name);

        return [
            "category" => $category,
            "channels" => $collection->getChannelsListByCategory($category["category_id"], $offset, $limit)
        ];
    }
} 