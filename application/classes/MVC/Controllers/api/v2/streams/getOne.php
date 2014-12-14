<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:53
 */

namespace MVC\Controllers\api\v2\streams;

use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpGet;
use MVC\Services\HttpResponse;
use REST\Streams;

class getOne extends Controller {
    public function doGet(HttpGet $get, HttpResponse $response, Streams $streams) {
        $id = $get->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        $response->setData($streams->getOneStream($id));
    }
} 