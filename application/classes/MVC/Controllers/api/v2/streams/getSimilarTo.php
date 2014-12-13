<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 22:45
 */

namespace MVC\Controllers\api\v2\streams;


use MVC\Exceptions\ControllerException;
use MVC\Services\HttpGet;
use MVC\Services\HttpResponse;
use REST\Streams;

class getSimilarTo {
    public function doGet(HttpGet $get, HttpResponse $response, Streams $streams) {
        $id = $get->getParameter("id")
            ->getOrElseThrow(new ControllerException("id parameter is not specified"));

        $response->setData($streams->getSimilarTo($id));
    }
} 