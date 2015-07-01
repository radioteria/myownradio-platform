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
use Framework\Exceptions\ControllerException;
use Framework\Services\Http\HttpGet;
use Framework\Services\JsonResponse;
use Objects\Category;
use Tools\Optional\Filter;

class DoCategory implements Controller {
    public function doGet(JsonResponse $response, HttpGet $get, ChannelsCollection $collection) {

        $category_name  = $get->getOrError("category_name");

        $offset         = $get->get("offset")
                              ->filter(Filter::isNumber())
                              ->orZero();

        $limit          = $get->get("limit")
                              ->filter(Filter::isNumber())
                              ->getOrElse(ChannelsCollection::CHANNELS_PER_REQUEST_MAX);

        /**
         * @var Category $category
         */
        $category = Category::getByFilter("key", [":key" => $category_name])
            ->getOrThrow(ControllerException::tr("VALIDATOR_INVALID_CATEGORY_NAME", [ $category_name ]));

        return [
            "category" => $category,
            "channels" => $collection->getChannelsListByCategory($category->getId(), $offset, $limit)
        ];
    }
} 