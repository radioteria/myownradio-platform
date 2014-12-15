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
use MVC\Services\JsonResponse;
use REST\Streams;

class DoGetSimilarTo {
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {
        $id = $get->getParameter("id")
            ->getOrElseThrow(new ControllerException("id parameter is not specified"));

        $response->setData($streams->getSimilarTo($id));
    }
} 