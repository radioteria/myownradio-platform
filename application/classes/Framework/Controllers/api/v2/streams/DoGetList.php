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
use Objects\Stream;
use REST\Streams;

class DoGetList implements Controller {
    /**
     * This method invoked on GET method
     */
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {

        $filter     = $get->getParameter("q")->getOrElseEmpty();
        $category   = $get->getParameter("c")->getOrElseNull();

        $offset     = $get->getParameter("from")->getOrElse(0);
        $limit      = $get->getParameter("limit")->getOrElse(50);

        //$streams = Stream::getList($limit, $offset);

        $response->setData($streams->getStreamListFiltered($filter, $category, $offset, $limit));
        //$response->setData(Stream::getList($limit, $offset));

    }

}