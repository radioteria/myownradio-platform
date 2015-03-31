<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:08
 */

namespace Framework\Controllers\api\v2\streams;

use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetList implements Controller {

    /**
     * This method invoked on GET method
     */
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {

        $filter = $get->getParameter("filter")->getOrElseNull();
        $category = $get->getParameter("category")->getOrElseNull();

        $offset = $get->getParameter("offset")->getOrElse(0);
        $limit = $get->getParameter("limit")->getOrElse(50);

        $response->setData($streams->getStreamListFiltered($filter, $category, $offset, $limit));

    }

}