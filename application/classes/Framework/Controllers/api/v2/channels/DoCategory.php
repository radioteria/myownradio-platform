<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:18
 */

namespace Framework\Controllers\api\v2\channels;


use API\ChannelsCollection;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\Validators\CategoryValidator;

class DoCategory implements Controller {
    public function doGet(HttpGet $get, ChannelsCollection $collection, CategoryValidator $validator, JsonResponse $response) {
        $category_name = $get->getRequired("category_name");
        $offset = $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse(0);
        $limit = $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElseNull();

        $category_id = $validator->validateChannelCategoryByPermalink($category_name);

        $response->setData($collection->getChannelsListByCategory($category_id, $offset, $limit));
    }
} 