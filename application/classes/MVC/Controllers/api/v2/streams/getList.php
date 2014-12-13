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
use MVC\Services\HttpResponse;
use REST\Streams;

class getList extends Controller {
    /**
     * This method executes on GET method
     */
    public function doGet(HttpGet $get, HttpResponse $response, Streams $streams) {
        $filter     = $get->getParameter("q")->getOrElseEmpty();
        $category   = $get->getParameter("c")->getOrElseNull();

        $from       = $get->getParameter("from")->getOrElse(0);
        $limit      = $get->getParameter("limit")->getOrElse(50);

        $response->setData($streams->getStreamListFiltered($filter, $category, $from, $limit));
    }

    public function doPost() {
        throw new NotImplementedException();
    }
}