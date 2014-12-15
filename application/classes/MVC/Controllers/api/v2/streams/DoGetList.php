<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 22:08
 */

namespace MVC\Controllers\api\v2\streams;

use MVC\Controller;
use MVC\Exceptions\NotImplementedException;
use MVC\Services\HttpGet;
use MVC\Services\JsonResponse;
use REST\Streams;

class DoGetList extends Controller {
    /**
     * This method invoked on GET method
     */
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {
        $filter     = $get->getParameter("q")->getOrElseEmpty();
        $category   = $get->getParameter("c")->getOrElseNull();

        $from       = $get->getParameter("from")->getOrElse(0);
        $limit      = $get->getParameter("limit")->getOrElse(50);

        $response->setData($streams->getStreamListFiltered($filter, $category, $from, $limit));
    }

    /*
     * This method invoked on POST method
     */
    public function doPost() {
        throw new NotImplementedException();
    }
}