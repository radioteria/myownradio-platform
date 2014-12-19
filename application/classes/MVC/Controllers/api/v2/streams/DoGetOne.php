<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:53
 */

namespace MVC\Controllers\api\v2\streams;

use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpGet;
use MVC\Services\JsonResponse;
use REST\Streams;

class DoGetOne extends Controller {
    public function doGet(HttpGet $get, JsonResponse $response, Streams $streams) {
        $id = $get->getParameter("id")
            ->getOrElseThrow(ControllerException::noArgument("id"));

        $response->setData($streams->getOneStream($id));
    }
} 