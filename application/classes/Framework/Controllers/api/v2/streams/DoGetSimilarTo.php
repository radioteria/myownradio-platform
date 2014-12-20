<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:45
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetSimilarTo extends Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {
        $id = $get->getParameter("id")
            ->getOrElseThrow(new ControllerException("id parameter is not specified"));

        $response->setData($streams->getSimilarTo($id));
    }
} 