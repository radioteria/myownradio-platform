<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 23:03
 */

namespace Framework\Controllers\api\v2\streams;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use REST\Streams;

class DoGetStatus implements Controller {
    public function doGet(HttpGet $get, Streams $streams, JsonResponse $response) {
        $id = $get->getParameter("id")->getOrElseThrow(ControllerException::noArgument("id"));
        $status = $streams->getStreamStatus($id);
        $response->setData($status);
    }
} 